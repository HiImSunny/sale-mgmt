<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\VNPayQRService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class POSController extends Controller
{
    protected VNPayQRService $vnpayQRService;

    public function __construct(VNPayQRService $vnpayQRService)
    {
        $this->vnpayQRService = $vnpayQRService;
    }

    public function index()
    {
        return view('pos');
    }

    public function createOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash_at_counter,vnpay'
        ]);

        DB::beginTransaction();

        try {
            $subtotal = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                if (isset($item['variant_id']) && $item['variant_id']) {
                    $variant = ProductVariant::with(['product', 'attributeValues.attributeValue.attribute'])->find($item['variant_id']);

                    if (!$variant) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Variant không tồn tại'
                        ], 400);
                    }

                    if ($variant->stock_quantity < $item['quantity']) {
                        return response()->json([
                            'success' => false,
                            'message' => "Sản phẩm {$variant->product->name} không đủ tồn kho. Còn lại: {$variant->stock_quantity}"
                        ], 400);
                    }

                    $lineTotal = $item['quantity'] * $item['unit_price'];
                    $subtotal += $lineTotal;

                    $orderItems[] = [
                        'type' => 'variant',
                        'variant' => $variant,
                        'product' => $variant->product,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'line_total' => $lineTotal
                    ];

                } elseif (isset($item['product_id']) && $item['product_id']) {
                    // Handle simple product
                    $product = Product::find($item['product_id']);

                    if (!$product || $product->has_variants) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Sản phẩm không tồn tại hoặc có biến thể'
                        ], 400);
                    }

                    if ($product->stock_quantity < $item['quantity']) {
                        return response()->json([
                            'success' => false,
                            'message' => "Sản phẩm {$product->name} không đủ tồn kho. Còn lại: {$product->stock_quantity}"
                        ], 400);
                    }

                    $lineTotal = $item['quantity'] * $item['unit_price'];
                    $subtotal += $lineTotal;

                    $orderItems[] = [
                        'type' => 'product',
                        'product' => $product,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'line_total' => $lineTotal
                    ];
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Phải có product_id hoặc variant_id'
                    ], 400);
                }
            }

            // Create order
            $order = Order::create([
                'user_id' => Auth::id(),
                'code' => $this->generateOrderCode('POS'),
                'payment_method' => $request->payment_method,
                'payment_status' => 'unpaid',
                'status' => 'pending',
                'type' => 'sale',
                'subtotal' => $subtotal,
                'discount_total' => 0,
                'grand_total' => $subtotal,
                'notes' => 'Đơn hàng POS tại quầy'
            ]);

            foreach ($orderItems as $itemData) {
                if ($itemData['type'] === 'variant') {
                    $variant = $itemData['variant'];

                    $attributesSnapshot = $variant->attributeValues->map(function ($attrValue) {
                        return [
                            'attribute_name' => $attrValue->attributeValue->attribute->name,
                            'value' => $attrValue->attributeValue->value
                        ];
                    })->toArray();

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $variant->product_id,
                        'product_variant_id' => $variant->id,
                        'name_snapshot' => $variant->product->name,
                        'sku_snapshot' => $variant->sku,
                        'unit_price' => $itemData['unit_price'],
                        'quantity' => $itemData['quantity'],
                        'line_total' => $itemData['line_total'],
                        'attributes_snapshot' => json_encode($attributesSnapshot)
                    ]);
                } else {
                    // Simple product order item
                    $product = $itemData['product'];

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_variant_id' => null,
                        'name_snapshot' => $product->name,
                        'sku_snapshot' => $product->sku,
                        'unit_price' => $itemData['unit_price'],
                        'quantity' => $itemData['quantity'],
                        'line_total' => $itemData['line_total'],
                        'attributes_snapshot' => json_encode([])
                    ]);
                }
            }

            DB::commit();

            $response = [
                'success' => true,
                'message' => 'Đơn hàng đã được tạo thành công',
                'data' => [
                    'order_id' => $order->id,
                    'order_code' => $order->code,
                    'total' => $order->grand_total,
                    'payment_method' => $order->payment_method
                ]
            ];

            if ($request->payment_method === 'vnpay') {
                $qrData = $this->vnpayQRService->generateVNPayLink(
                    $order->id,
                    $order->grand_total,
                    "Thanh toán đơn hàng POS #{$order->code}"
                );

                $response['data']['vnpay_qr'] = $qrData;
            }

            return response()->json($response);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkPaymentStatus($order)
    {
        try {
            $order = Order::findOrFail($order);

            return response()->json([
                'success' => true,
                'payment_status' => $order->payment_status,
                'order_code' => $order->code,
                'amount' => $order->subtotal,
                'payment_method' => $order->payment_method,
                'invoice_url' => $order->invoice_url ?? null,
                'updated_at' => $order->updated_at
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng hoặc có lỗi xảy ra'
            ], 404);
        }
    }

    /**
     * @throws \Throwable
     */
    public function confirmPayment(Request $request, Order $order)
    {
        logger()->info(-1);
        $request->validate([
            'payment_method' => 'required|in:cash_at_counter,vnpay'
        ]);

        logger()->info(0);

        if ($order->payment_status === 'paid') {
            if ($order->payment_method === 'vnpay') {
                logger()->info(1);
                return response()->json([
                    'success' => true,
                    'message' => 'Đơn hàng đã được thanh toán'
                ]);
            }
            logger()->info(2);
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng đã được thanh toán'
            ], 400);
        }

        logger()->info(3);

        DB::beginTransaction();

        try {
            $order->update([
                'payment_status' => 'paid',
                'paid_at' => now()
            ]);

            $this->confirmOrderAndReduceStock($order);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Thanh toán thành công',
                'data' => [
                    'order_id' => $order->id,
                    'invoice_url' => route('orders.invoice', $order)
                ]
            ]);

        } catch (Exception $e) {
            logger()->info(4);
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xác nhận thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }


    private function confirmOrderAndReduceStock(Order $order)
    {
        $order->update(['status' => 'completed']);

        foreach ($order->items as $item) {
            if ($item->product_variant_id) {
                $variant = $item->productVariant;

                $variant->decrement('stock_quantity', $item->quantity);

                InventoryTransaction::create([
                    'product_variant_id' => $variant->id,
                    'type' => 'out',
                    'qty' => $item->quantity,
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'user_id' => Auth::id(),
                    'note' => "Bán hàng POS - Đơn {$order->code}"
                ]);
            } elseif ($item->product_id) {
                $product = Product::find($item->product_id);

                if ($product && !$product->has_variants) {
                    $product->decrement('stock_quantity', $item->quantity);

                }
            }
        }
    }

    public function searchOrders(Request $request)
    {
        $query = $request->get('q');

        $orders = Order::with(['items'])
            ->where('type', 'sale')
            ->where('status', 'completed')
            ->where('payment_status', 'paid')
            ->where(function ($q) use ($query) {
                $q->where('code', 'like', "%{$query}%")
                    ->orWhere('id', $query);
            })
            ->withSum('refundOrders', 'grand_total')
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'code' => $order->code,
                    'grand_total' => $order->grand_total,
                    'total_refunded' => $order->refund_orders_sum_grand_total ?? 0,
                    'remaining_refundable' => $order->grand_total - ($order->refund_orders_sum_grand_total ?? 0),
                    'created_at' => $order->created_at->format('d/m/Y H:i'),
                    'items_count' => $order->items->count()
                ];
            })
        ]);
    }

    public function getOrderDetails($orderId)
    {
        $order = Order::with(['items.productVariant'])
            ->where('type', 'sale')
            ->where('status', 'completed')
            ->where('payment_status', 'paid')
            ->find($orderId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng'
            ], 404);
        }

        $totalRefunded = $order->refundOrders()->sum('grand_total');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'code' => $order->code,
                'grand_total' => $order->grand_total,
                'total_refunded' => $totalRefunded,
                'remaining_refundable' => $order->grand_total - $totalRefunded,
                'created_at' => $order->created_at->format('d/m/Y H:i'),
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name_snapshot' => $item->name_snapshot,
                        'sku_snapshot' => $item->sku_snapshot,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'line_total' => $item->line_total
                    ];
                })
            ]
        ]);
    }

    public function createRefund(Request $request)
    {
        $request->validate([
            'parent_order_id' => 'required|exists:orders,id',
            'items' => 'required|array|min:1',
            'items.*.original_item_id' => 'required|exists:order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'refund_reason' => 'required|string',
            'refund_reason_detail' => 'nullable|string',
            'payment_method' => 'required|string'
        ]);

        DB::beginTransaction();

        try {
            $parentOrder = Order::findOrFail($request->parent_order_id);

            if ($parentOrder->type !== 'sale' || $parentOrder->status !== 'completed') {
                throw new Exception('Đơn hàng không thể hoàn trả');
            }

            $refundTotal = 0;
            $refundItems = [];

            foreach ($request->items as $itemData) {
                $originalItem = OrderItem::find($itemData['original_item_id']);

                if (!$originalItem || $originalItem->order_id != $parentOrder->id) {
                    throw new Exception('Item không thuộc đơn hàng này');
                }

                if ($itemData['quantity'] > $originalItem->quantity) {
                    throw new Exception('Số lượng hoàn trả vượt quá số lượng đã mua');
                }

                $lineTotal = $itemData['quantity'] * $itemData['unit_price'];
                $refundTotal += $lineTotal;

                $refundItems[] = [
                    'original_item' => $originalItem,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'line_total' => $lineTotal
                ];
            }

            $refundOrder = Order::create([
                'type' => 'refund',
                'parent_order_id' => $parentOrder->id,
                'user_id' => Auth::id(),
                'code' => $this->generateOrderCode('RF'),
                'payment_method' => $request->payment_method,
                'payment_status' => 'paid',
                'status' => 'completed',
                'subtotal' => $refundTotal,
                'grand_total' => $refundTotal,
                'refund_reason' => $request->refund_reason,
                'refund_reason_detail' => $request->refund_reason_detail,
                'notes' => 'Hoàn trả từ POS - Đơn gốc: ' . $parentOrder->code
            ]);

            foreach ($refundItems as $itemData) {
                $originalItem = $itemData['original_item'];

                OrderItem::create([
                    'order_id' => $refundOrder->id,
                    'product_id' => $originalItem->product_id,
                    'product_variant_id' => $originalItem->product_variant_id,
                    'name_snapshot' => $originalItem->name_snapshot,
                    'sku_snapshot' => $originalItem->sku_snapshot,
                    'unit_price' => $itemData['unit_price'],
                    'quantity' => $itemData['quantity'],
                    'line_total' => $itemData['line_total'],
                    'attributes_snapshot' => $originalItem->attributes_snapshot
                ]);

                if ($originalItem->product_variant_id) {
                    $variant = $originalItem->productVariant;
                    $variant->increment('stock_quantity', $itemData['quantity']);

                    InventoryTransaction::create([
                        'product_variant_id' => $variant->id,
                        'type' => 'in',
                        'qty' => $itemData['quantity'],
                        'reference_type' => 'refund',
                        'reference_id' => $refundOrder->id,
                        'user_id' => Auth::id(),
                        'note' => "Hoàn trả - #{$refundOrder->code}"
                    ]);
                } elseif ($originalItem->product_id) {
                    $product = Product::find($originalItem->product_id);

                    if ($product && !$product->has_variants) {
                        $product->increment('stock_quantity', $itemData['quantity']);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hoàn trả thành công',
                'data' => [
                    'refund_order_id' => $refundOrder->id,
                    'refund_code' => $refundOrder->code,
                    'refund_amount' => $refundTotal
                ]
            ]);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function generateOrderCode($prefix = 'POS')
    {
        do {
            $code = $prefix . date('Ymd') . rand(1000, 9999);
        } while (Order::where('code', $code)->exists());

        return $code;
    }
//
//    private function updateCustomerStats($customer)
//    {
//        try {
//            $completedSaleOrders = $customer->orders()
//                ->where('type', 'sale')
//                ->where('status', 'completed');
//
//            $completedRefundOrders = $customer->orders()
//                ->where('type', 'refund')
//                ->where('status', 'completed');
//
//            $totalSalesAmount = $completedSaleOrders->sum('grand_total');
//            $totalRefundAmount = $completedRefundOrders->sum('grand_total');
//            $netSpending = $totalSalesAmount - $totalRefundAmount;
//
//            $customer->update([
//                'total_orders' => $completedSaleOrders->count(),
//                'total_spent' => max(0, $netSpending),
//            ]);
//
//            $this->updateCustomerTier($customer);
//
//            Log::info("Customer stats updated after payment for customer {$customer->id}: Orders: {$customer->total_orders}, Spent: {$customer->total_spent}");
//
//        } catch (Exception $e) {
//            Log::error("Error updating customer stats for customer {$customer->id}: " . $e->getMessage());
//        }
//    }
//
//    private function updateCustomerTier($customer)
//    {
//        $oldTier = $customer->customer_tier;
//        $newTier = 'bronze';
//
//        if ($customer->total_spent >= 50000000) {
//            $newTier = 'platinum';
//        } elseif ($customer->total_spent >= 20000000) {
//            $newTier = 'gold';
//        } elseif ($customer->total_spent >= 5000000) {
//            $newTier = 'silver';
//        }
//
//        if ($oldTier !== $newTier) {
//            $customer->update(['customer_tier' => $newTier]);
//            Log::info("Customer tier updated after VNPay payment for customer {$customer->id}: {$oldTier} → {$newTier}");
//        }
//    }
}
