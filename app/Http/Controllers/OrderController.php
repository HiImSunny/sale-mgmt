<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\InventoryTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'user', 'items']);

        //  Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        //  Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        //  Filter by payment status
        if ($paymentStatus = $request->get('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        //  Filter by order type
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        //  Date range filter
        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        //  Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');

        $allowedSorts = ['code', 'grand_total', 'status', 'payment_status', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $orders = $query->paginate(20)->withQueryString();

        //  Statistics
        $stats = [
            'total' => Order::whereNot('type', 'refund')->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'canceled' => Order::where('status', 'canceled')->count(),
            'unpaid' => Order::where('payment_status', 'unpaid')->count(),
            'total_revenue' => Order::where('status', 'completed')
                    ->where('type', 'sale')
                    ->sum('grand_total')
                - Order::where('status', 'completed')
                    ->where('type', 'refund')
                    ->sum('grand_total'),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'refund_orders' => Order::where('type', 'refund')->count(),
        ];

        return view('order.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        //  Load relationships
        $order->load([
            'customer',
            'user',
            'items.product',
            'items.productVariant',
            'parentOrder', // For refund orders
            'refundOrders' // For sale orders that have been refunded
        ]);

        //  Calculate additional stats
        $orderStats = [
            'items_count' => $order->items->count(),
            'total_quantity' => $order->items->sum('quantity'),
            'profit_margin' => $this->calculateProfitMargin($order),
            'days_since_order' => $order->created_at->diffInDays(now()),
        ];

        return view('order.show', compact('order', 'orderStats'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,canceled',
            'payment_status' => 'required|in:unpaid,paid,failed',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($order, $validated) {
            $oldStatus = $order->status;
            $oldPaymentStatus = $order->payment_status;

            $order->update($validated);

            if ($oldPaymentStatus !== 'paid' && $validated['payment_status'] === 'paid') {
                $order->update(['paid_at' => now()]);
            }

            if ($oldStatus !== 'completed' && $validated['status'] === 'completed' && $order->customer) {
                $this->updateCustomerStats($order->customer);
            }

            if ($oldStatus !== 'completed' && $validated['status'] === 'completed') {
                $this->handleInventoryOut($order);
            }

            if ($oldStatus === 'completed' && $validated['status'] === 'canceled') {
                $this->handleInventoryIn($order);
            }

            if ($oldStatus === 'pending' && $validated['status'] === 'canceled') {
                $this->handleInventoryIn($order);
            }
        });

        return back()->with('swal_success', 'Trạng thái đơn hàng đã được cập nhật!');
    }

    public function createRefund(Request $request, Order $order)
    {
        //  Validate refund request
        if ($order->type !== 'sale' || $order->status !== 'completed') {
            return back()->with('swal_error', 'Chỉ có thể hoàn trả đơn hàng đã hoàn thành!');
        }

        $validated = $request->validate([
            'refund_reason' => 'required|in:customer_request,damaged_product,wrong_product,quality_issue,other',
            'refund_reason_detail' => 'required|string|max:1000',
            'refund_items' => 'required|array',
            'refund_items.*.order_item_id' => 'required|exists:order_items,id',
            'refund_items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($order, $validated) {
            //  Calculate refund amount
            $refundAmount = 0;
            $refundItems = [];

            foreach ($validated['refund_items'] as $refundItem) {
                $orderItem = OrderItem::find($refundItem['order_item_id']);

                //  Validate order item belongs to this order
                if ($orderItem->order_id !== $order->id) {
                    throw new \Exception('Item không thuộc đơn hàng này');
                }

                $refundQty = min($refundItem['quantity'], $orderItem->quantity);
                $itemRefundAmount = ($orderItem->unit_price * $refundQty);

                $refundAmount += $itemRefundAmount;
                $refundItems[] = [
                    'order_id' => null, // Will be set after creating refund order
                    'product_id' => $orderItem->product_id,
                    'product_variant_id' => $orderItem->product_variant_id,
                    'name_snapshot' => $orderItem->name_snapshot,
                    'sku_snapshot' => $orderItem->sku_snapshot,
                    'unit_price' => $orderItem->unit_price,
                    'quantity' => $refundQty,
                    'line_total' => $itemRefundAmount,
                    'attributes_snapshot' => $orderItem->attributes_snapshot,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $refundItems[count($refundItems) - 1]['original_order_item'] = $orderItem;
            }

            //  Create refund order
            $refundOrder = Order::create([
                'user_id' => auth()->id(),
                'customer_id' => $order->customer_id,
                'code' => $this->generateOrderCode('RF'),
                'payment_method' => $order->payment_method,
                'payment_status' => 'paid', // Refunds are immediately "paid"
                'status' => 'completed',
                'subtotal' => $refundAmount,
                'discount_total' => 0,
                'grand_total' => $refundAmount,
                'paid_at' => now(),
                'notes' => 'Hoàn trả từ đơn hàng: ' . $order->code,
                'type' => 'refund',
                'parent_order_id' => $order->id,
                'refund_reason' => $validated['refund_reason'],
                'refund_reason_detail' => $validated['refund_reason_detail'],
            ]);

            foreach ($refundItems as &$item) {
                $item['order_id'] = $refundOrder->id;
                $originalOrderItem = $item['original_order_item'];
                unset($item['original_order_item']); // Remove before insert

                // Create the refund order item
                OrderItem::create($item);

                if ($originalOrderItem->product_variant_id) {
                    $this->restoreInventoryForRefund($originalOrderItem, $item['quantity'], $refundOrder);
                }
            }

            if ($order->customer) {
                $this->updateCustomerStats($order->customer);
            }

        });

        return back()->with('swal_success', 'Đơn hoàn trả đã được tạo thành công!');
    }

    public function invoice(Order $order)
    {
        try {
            // Validate order type
            if ($order->type !== 'sale') {
                return back()->with('swal_error', 'Chỉ có thể in hóa đơn bán hàng cho đơn hàng có loại "sale"!');
            }

            if (!in_array($order->status, ['pending', 'completed', 'confirmed', 'processing'])) {
                return back()->with('swal_error', 'Trạng thái đơn hàng không hợp lệ để xuất hóa đơn bán hàng!');
            }

            // Load necessary relationships
            $order->load([
                'user',
                'items.product',
                'items.productVariant',
                'customer'
            ]);

            // Company information
            $company = [
                'name' => config('app.company_name', 'PacificStore'),
                'address' => config('app.company_address', '168 Nguyễn Văn Cừ Nối Dài, An Bình, Ninh Kiều, Cần Thơ'),
                'phone' => config('app.company_phone', '0292 3798 668'),
                'email' => config('app.company_email', 'info@pacific.store'),
            ];

            // Payment method mapping
            $paymentMethods = [
                'cash_at_counter' => 'Tiền mặt',
                'vnpay' => 'VNPay',
            ];

            // Status mapping
            $statusMapping = [
                'pending' => 'Chờ xử lý',
                'completed' => 'Hoàn tất',
                'cancelled' => 'Đã hủy',
            ];

            // Create invoice ARRAY for template compatibility
            $invoice = [
                'invoice_number' => $order->code,
                'invoice_date' => $order->created_at,
                'due_date' => $order->created_at->addDays(7),
                'status' => $order->status,
                'status_label' => $statusMapping[$order->status] ?? $order->status,
                'payment_method' => $paymentMethods[$order->payment_method] ?? ($order->payment_method ? ucfirst($order->payment_method) : 'Không xác định'),
                'payment_status' => $order->payment_status,
                'created_at' => $order->created_at,
                'createdBy' => auth()->user() ?? (object)['name' => 'System', 'position' => 'Nhân viên bán hàng'],

                // Customer information (array thay vì object)
                'customer' => [
                    'name' => $order->customer->name ?? 'Khách vãng lai',
                    'phone' => $order->customer->phone ?? 'N/A',
                    'email' => $order->customer->email ?? 'N/A',
                    'address' => $order->customer->address ?? 'N/A',
                ],

                // Invoice items (array thay vì object)
                'invoiceItems' => $order->items->map(function ($item) {
                    $unitPrice = $item->unit_price;
                    $quantity = $item->quantity;
                    $discountAmount = $item->discount_amount ?? 0;
                    $discountPercent = $item->discount_percent ?? 0;

                    // Tính total price sau discount
                    $totalPrice = ($unitPrice * $quantity);
                    if ($discountPercent > 0) {
                        $totalPrice -= $totalPrice * ($discountPercent / 100);
                    } else {
                        $totalPrice -= $discountAmount;
                    }

                    // ✅ FIXED: Get product name from correct source
                    $productName = $item->name_snapshot ??
                        ($item->product ? $item->product->name : 'N/A');

                    $productSku = $item->sku_snapshot ??
                        ($item->product ? $item->product->sku : '');

                    return [
                        'product' => [
                            'name' => $productName,
                            'sku' => $productSku,
                            'unit' => 'Cái' // Default unit
                        ],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'discount_percent' => $discountPercent,
                        'discount_amount' => $discountAmount,
                        'total_price' => $totalPrice,
                    ];
                })->toArray(),

                // Totals calculation
                'subtotal' => $order->subtotal ?? $order->items->sum(function ($item) {
                        return $item->quantity * $item->unit_price;
                    }),
                'total_discount' => $order->total_discount ?? $order->items->sum(function ($item) {
                        if ($item->discount_percent > 0) {
                            return ($item->quantity * $item->unit_price) * ($item->discount_percent / 100);
                        }
                        return $item->discount_amount ?? 0;
                    }),
                'total_amount' => $order->grand_total,
                'notes' => $order->notes ?? '',

            ];

            // Generate PDF
            $pdf = Pdf::loadView('pdf.invoice', compact('order', 'company', 'invoice'));

            // Configure PDF settings
            $pdf->setPaper('A4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'debugKeepTemp' => false,
                    'debugCss' => false,
                    'debugLayout' => false,
                ]);

            // Generate filename
            $date = $order->created_at->format('Y-m-d');
            $filename = "hoa-don-ban-hang-{$order->code}-{$date}.pdf";

            // Return PDF stream
            return $pdf->stream($filename);

        } catch (\Exception $e) {
            \Log::error('Invoice generation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('swal_error', 'Có lỗi xảy ra khi tạo hóa đơn bán hàng. Vui lòng thử lại sau!');
        }
    }

    public function refundInvoice(Order $order)
    {
        try {
            // Validate order type
            if ($order->type !== 'refund') {
                return back()->with('swal_error', 'Chỉ có thể in hóa đơn hoàn hàng cho đơn hàng có loại "refund"!');
            }

            if (!in_array($order->status, ['pending', 'completed', 'cancelled'])) {
                return back()->with('swal_error', 'Trạng thái đơn hàng không hợp lệ để xuất hóa đơn hoàn hàng!');
            }

            // Load necessary relationships
            $order->load([
                'user',
                'items.product',
                'items.productVariant',
                'customer',
                'parentOrder.user',
                'parentOrder.items.product',
                'parentOrder.items.productVariant',
                'parentOrder.customer'
            ]);

            // Validate parent order exists
            if (!$order->parentOrder) {
                return back()->with('swal_error', 'Không tìm thấy đơn hàng gốc để thực hiện hoàn hàng!');
            }

            // Company information
            $company = [
                'name' => config('app.company_name', 'PacificStore'),
                'address' => config('app.company_address', '168 Nguyễn Văn Cừ Nối Dài, An Bình, Ninh Kiều, Cần Thơ'),
                'phone' => config('app.company_phone', '0292 3798 668'),
                'email' => config('app.company_email', 'info@pacific.store'),
            ];

            // Payment method mapping
            $paymentMethods = [
                'cash_at_counter' => 'Tiền mặt',
                'vnpay' => 'VNPay',
            ];

            // Create return ARRAY for template compatibility
            $return = [
                'return_code' => $order->code,
                'return_date' => $order->created_at,
                'status' => $order->status,
                'reason' => $this->getRefundReasonText($order->refund_reason),
                'reason_detail' => $order->refund_reason_detail ?? 'Không có ghi chú chi tiết',
                'total_return_amount' => $order->grand_total,
                'payment_method' => $paymentMethods[$order->payment_method] ?? ($order->payment_method ? ucfirst($order->payment_method) : 'Không xác định'),
                'payment_status' => $order->payment_status,
                'created_at' => $order->created_at,
                'processedBy' => auth()->user() ?? (object)['name' => 'System'],

                // Original invoice information (array thay vì object)
                'originalInvoice' => [
                    'invoice_number' => $order->parentOrder->code,
                    'created_at' => $order->parentOrder->created_at,
                    'customer_name' => $order->parentOrder->customer->name ?? 'Khách vãng lai',
                    'customer_phone' => $order->parentOrder->customer->phone ?? 'N/A',
                    'customer_address' => $order->parentOrder->customer->address ?? 'N/A',
                    'total_amount' => $order->parentOrder->grand_total,
                ],

                // Return items (array thay vì object)
                'returnItems' => $order->items->map(function ($item) {
                    // ✅ FIXED: Get product name from correct source
                    $productName = $item->name_snapshot ??
                        ($item->product ? $item->product->name : 'N/A');

                    return [
                        'product' => [
                            'name' => $productName
                        ],
                        'quantity_returned' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->line_total,
                        'item_condition' => $item->condition ?? 'Bình thường',
                    ];
                })->toArray()
            ];

            // Generate PDF
            $pdf = Pdf::loadView('pdf.refund-invoice', compact('order', 'company', 'return'));

            // Configure PDF settings
            $pdf->setPaper('A4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'debugKeepTemp' => false,
                    'debugCss' => false,
                    'debugLayout' => false,
                ]);

            // Generate filename
            $date = $order->created_at->format('Y-m-d');
            $filename = "hoa-don-hoan-hang-{$order->code}-{$date}.pdf";

            // Return PDF stream
            return $pdf->stream($filename);

        } catch (\Exception $e) {
            \Log::error('Refund invoice generation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('swal_error', 'Có lỗi xảy ra khi tạo hóa đơn hoàn hàng. Vui lòng thử lại sau!');
        }
    }

    public function getRefundReasonText($reason)
    {
        $reasons = [
            'customer_request' => 'Khách hàng yêu cầu hoàn hàng',
            'damaged_product' => 'Sản phẩm bị hư hỏng',
            'wrong_product' => 'Giao nhầm sản phẩm',
            'quality_issue' => 'Vấn đề về chất lượng',
            'other' => 'Lý do khác'
        ];

        return $reasons[$reason] ?? 'Không xác định';
    }

    // ✅ FIXED: Inventory methods với stock_quantity
    private function handleInventoryOut(Order $order)
    {
        try {
            foreach ($order->items as $orderItem) {
                // ✅ FIXED: Handle both simple products and variants
                if ($orderItem->product_variant_id) {
                    // Handle variant inventory
                    $variant = ProductVariant::find($orderItem->product_variant_id);
                    if ($variant) {
                        if ($variant->stock_quantity < $orderItem->quantity) {
                            Log::warning("Insufficient stock for variant {$variant->id}. Available: {$variant->stock_quantity}, Required: {$orderItem->quantity}");
                        }

                        $variant->decrement('stock_quantity', $orderItem->quantity);

                        InventoryTransaction::create([
                            'product_variant_id' => $variant->id,
                            'type' => 'out',
                            'qty' => $orderItem->quantity,
                            'reference_type' => 'order',
                            'reference_id' => $order->id,
                            'user_id' => auth()->id(),
                            'note' => "Đơn hàng hoàn thành - #{$order->code}"
                        ]);

                        Log::info("Inventory reduced for variant {$variant->id}: -{$orderItem->quantity} (Order: {$order->code})");
                    }
                } elseif ($orderItem->product_id) {
                    // ✅ FIXED: Handle simple product inventory
                    $product = Product::find($orderItem->product_id);
                    if ($product && !$product->has_variants) {
                        if ($product->stock_quantity < $orderItem->quantity) {
                            Log::warning("Insufficient stock for product {$product->id}. Available: {$product->stock_quantity}, Required: {$orderItem->quantity}");
                        }

                        $product->decrement('stock_quantity', $orderItem->quantity);

                        // Note: You might want to create inventory transaction for products too
                        Log::info("Inventory reduced for product {$product->id}: -{$orderItem->quantity} (Order: {$order->code})");
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Error handling inventory out for order {$order->id}: " . $e->getMessage());
            throw $e;
        }
    }

    private function handleInventoryIn(Order $order)
    {
        try {
            foreach ($order->items as $orderItem) {
                // ✅ FIXED: Handle both simple products and variants
                if ($orderItem->product_variant_id) {
                    // Handle variant inventory
                    $variant = ProductVariant::find($orderItem->product_variant_id);
                    if ($variant) {
                        $variant->increment('stock_quantity', $orderItem->quantity);

                        InventoryTransaction::create([
                            'product_variant_id' => $variant->id,
                            'type' => 'in',
                            'qty' => $orderItem->quantity,
                            'reference_type' => 'order',
                            'reference_id' => $order->id,
                            'user_id' => auth()->id(),
                            'note' => "Đơn hàng bị hủy - #{$order->code}"
                        ]);

                        Log::info("Inventory restored for variant {$variant->id}: +{$orderItem->quantity} (Order: {$order->code})");
                    }
                } elseif ($orderItem->product_id) {
                    // ✅ FIXED: Handle simple product inventory
                    $product = Product::find($orderItem->product_id);
                    if ($product && !$product->has_variants) {
                        $product->increment('stock_quantity', $orderItem->quantity);

                        Log::info("Inventory restored for product {$product->id}: +{$orderItem->quantity} (Order: {$order->code})");
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Error handling inventory in for order {$order->id}: " . $e->getMessage());
            throw $e;
        }
    }

    private function restoreInventoryForRefund(OrderItem $originalOrderItem, int $refundQuantity, Order $refundOrder)
    {
        try {
            // ✅ FIXED: Handle both simple products and variants
            if ($originalOrderItem->product_variant_id) {
                // Handle variant inventory
                $variant = ProductVariant::find($originalOrderItem->product_variant_id);
                if ($variant) {
                    $variant->increment('stock_quantity', $refundQuantity);

                    InventoryTransaction::create([
                        'product_variant_id' => $variant->id,
                        'type' => 'in',
                        'qty' => $refundQuantity,
                        'reference_type' => 'return',
                        'reference_id' => $refundOrder->id,
                        'user_id' => auth()->id(),
                        'note' => "Hoàn trả - #{$refundOrder->code} (Gốc: #{$originalOrderItem->order->code})"
                    ]);

                    Log::info("Inventory restored for refund - Variant {$variant->id}: +{$refundQuantity} (Refund: {$refundOrder->code})");
                }
            } elseif ($originalOrderItem->product_id) {
                // ✅ FIXED: Handle simple product inventory
                $product = Product::find($originalOrderItem->product_id);
                if ($product && !$product->has_variants) {
                    $product->increment('stock_quantity', $refundQuantity);

                    Log::info("Inventory restored for refund - Product {$product->id}: +{$refundQuantity} (Refund: {$refundOrder->code})");
                }
            }
        } catch (\Exception $e) {
            Log::error("Error restoring inventory for refund {$refundOrder->id}: " . $e->getMessage());
            throw $e;
        }
    }

    //  HELPER METHODS
    private function calculateProfitMargin(Order $order)
    {
        // ✅ FIXED: Calculate profit with new structure
        $totalCost = $order->items->sum(function ($item) {
            if ($item->productVariant) {
                // Use variant cost if available, otherwise estimate
                $costPrice = $item->productVariant->cost ?? ($item->unit_price * 0.7);
            } elseif ($item->product) {
                // Use product cost if available, otherwise estimate
                $costPrice = $item->product->cost ?? ($item->unit_price * 0.7);
            } else {
                // Fallback estimate
                $costPrice = $item->unit_price * 0.7;
            }

            return $item->quantity * $costPrice;
        });

        return $order->subtotal - $totalCost;
    }

    private function updateCustomerStats(Customer $customer)
    {
        try {
            $completedSaleOrders = $customer->orders()
                ->where('type', 'sale')
                ->where('status', 'completed');

            $completedRefundOrders = $customer->orders()
                ->where('type', 'refund')
                ->where('status', 'completed');

            $totalSalesAmount = $completedSaleOrders->sum('grand_total');
            $totalRefundAmount = $completedRefundOrders->sum('grand_total');
            $netSpending = $totalSalesAmount - $totalRefundAmount;

            $customer->update([
                'total_orders' => $completedSaleOrders->count(),
                'total_spent' => max(0, $netSpending), // Ensure non-negative
            ]);

            //  Update customer tier based on new spending
            $this->updateCustomerTier($customer);

            Log::info("Customer stats updated for customer {$customer->id}: Orders: {$customer->total_orders}, Spent: {$customer->total_spent}");

        } catch (\Exception $e) {
            Log::error("Error updating customer stats for customer {$customer->id}: " . $e->getMessage());
        }
    }

    private function updateCustomerTier(Customer $customer)
    {
        $oldTier = $customer->customer_tier;
        $newTier = 'bronze';

        if ($customer->total_spent >= 50000000) { // 50M VND
            $newTier = 'platinum';
        } elseif ($customer->total_spent >= 20000000) { // 20M VND
            $newTier = 'gold';
        } elseif ($customer->total_spent >= 5000000) { // 5M VND
            $newTier = 'silver';
        }

        if ($oldTier !== $newTier) {
            $customer->update(['customer_tier' => $newTier]);
            Log::info("Customer tier updated for customer {$customer->id}: {$oldTier} → {$newTier}");
        }
    }

    private function generateOrderCode($prefix = 'ORD')
    {
        do {
            $code = $prefix . date('Ymd') . rand(1000, 9999);
        } while (Order::where('code', $code)->exists());

        return $code;
    }
}
