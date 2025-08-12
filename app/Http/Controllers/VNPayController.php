<?php
// app/Http/Controllers/VNPayController.php

namespace App\Http\Controllers;

use App\Services\VNPayQRService;
use App\Models\Order;
use App\Models\Product;
use App\Models\InventoryTransaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VNPayController extends Controller
{
    protected VNPayQRService $vnpayService;

    public function __construct(VNPayQRService $vnpayService)
    {
        $this->vnpayService = $vnpayService;
    }

    public function handleReturn(Request $request)
    {
        try {
            $vnp_ResponseCode = $request->get('vnp_ResponseCode');
            $vnp_TxnRef = $request->get('vnp_TxnRef');
            $source = $request->get('source', 'pos');

            // Verify signature
            if (!$this->vnpayService->verifyPayment($request)) {
                return $this->returnResponse($source, false, 'Chữ ký không hợp lệ');
            }

            // Get order from TxnRef
            $order = $this->getOrderFromTxnRef($vnp_TxnRef);
            if (!$order) {
                return $this->returnResponse($source, false, 'Không tìm thấy đơn hàng');
            }

            if ($vnp_ResponseCode == '00') {
                // Payment success
                DB::transaction(function () use ($order) {
                    $this->processSuccessfulPayment($order);
                });

                return $this->returnResponse($source, true, 'Thanh toán thành công', $order);
            } else {
                // Payment failed
                $order->update(['payment_status' => 'failed']);
                return $this->returnResponse($source, false, 'Thanh toán thất bại', $order);
            }

        } catch (Exception $e) {
            Log::error('VNPAY Return Error: ' . $e->getMessage());
            return $this->returnResponse($source ?? 'pos', false, 'Có lỗi xảy ra');
        }
    }

    private function returnResponse($source, $success, $message, $order = null)
    {
        if ($source == 'pos') {
            return view('vnpay.pos-result', compact('success', 'message', 'order'));
        }

        return back();
    }

    private function getOrderFromTxnRef($txnRef)
    {
        // Extract order ID from POS_<order_id>_<timestamp> format
        if (str_starts_with($txnRef, 'POS_')) {
            $parts = explode('_', $txnRef);
            if (count($parts) >= 2) {
                $orderId = $parts[1];
                return Order::find($orderId);
            }
        }

        return null;
    }

    public function handleIPN(Request $request)
    {
        try {
            $vnp_ResponseCode = $request->get('vnp_ResponseCode');
            $vnp_TxnRef = $request->get('vnp_TxnRef');

            // Verify signature
            if (!$this->vnpayService->verifyPayment($request)) {
                return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
            }

            // Get order
            $order = $this->getOrderFromTxnRef($vnp_TxnRef); // ✅ FIXED: Use local method instead of service method

            if (!$order) {
                return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
            }

            if ($vnp_ResponseCode == '00') {
                if ($order->payment_status !== 'paid') {
                    DB::transaction(function () use ($order) {
                        $this->processSuccessfulPayment($order);
                    });
                }
                return response()->json(['RspCode' => '00', 'Message' => 'Success']);
            } else {
                $order->update(['payment_status' => 'failed']);
                return response()->json(['RspCode' => '00', 'Message' => 'Payment failed']);
            }
        } catch (Exception $e) {
            Log::error('VNPAY IPN Error: ' . $e->getMessage());
            return response()->json(['RspCode' => '99', 'Message' => 'System error']);
        }
    }

    private function processSuccessfulPayment(Order $order)
    {
        // Update order status - use 'completed' instead of 'confirmed'
        $order->update([
            'status' => 'completed', // ✅ FIXED: Use standard status
            'payment_status' => 'paid',
            'paid_at' => now() // ✅ ADDED: Set paid timestamp
        ]);

        // Create inventory transactions to reduce stock
        foreach ($order->items as $item) { // ✅ FIXED: Use 'items' relationship name
            if ($item->product_variant_id) {
                // ✅ Handle variant inventory
                $variant = $item->productVariant;

                if ($variant) {
                    // Check stock availability
                    if ($variant->stock_quantity < $item->quantity) {
                        Log::warning("Insufficient stock for variant {$variant->id}. Available: {$variant->stock_quantity}, Required: {$item->quantity}");
                        // Continue processing but log warning
                    }

                    // Reduce inventory - use stock_quantity instead of stock
                    $variant->decrement('stock_quantity', $item->quantity);

                    // Create inventory transaction
                    InventoryTransaction::create([
                        'product_variant_id' => $variant->id,
                        'type' => 'out',
                        'qty' => $item->quantity,
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'user_id' => $order->user_id, // ✅ ADDED: Track user
                        'note' => 'Bán hàng VNPay - Đơn #' . $order->code,
                    ]);

                    Log::info("VNPay payment processed - Variant {$variant->id} stock reduced by {$item->quantity} (Order: {$order->code})");
                }
            } elseif ($item->product_id) {
                // ✅ FIXED: Handle simple product inventory
                $product = Product::find($item->product_id);

                if ($product && !$product->has_variants) {
                    // Check stock availability
                    if ($product->stock_quantity < $item->quantity) {
                        Log::warning("Insufficient stock for product {$product->id}. Available: {$product->stock_quantity}, Required: {$item->quantity}");
                        // Continue processing but log warning
                    }

                    // Reduce inventory
                    $product->decrement('stock_quantity', $item->quantity);

                    Log::info("VNPay payment processed - Product {$product->id} stock reduced by {$item->quantity} (Order: {$order->code})");

                    // Note: You might want to create inventory transaction for products too
                    // if you need to track simple product inventory changes
                }
            }
        }

        // ✅ ADDED: Update customer stats if customer exists
        if ($order->customer) {
            $this->updateCustomerStats($order->customer);
        }
    }

    // ✅ ADDED: Helper method to update customer statistics
    private function updateCustomerStats($customer)
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

            // Update customer tier based on new spending
            $this->updateCustomerTier($customer);

            Log::info("Customer stats updated after VNPay payment for customer {$customer->id}: Orders: {$customer->total_orders}, Spent: {$customer->total_spent}");

        } catch (Exception $e) {
            Log::error("Error updating customer stats for customer {$customer->id}: " . $e->getMessage());
        }
    }

    // ✅ ADDED: Helper method to update customer tier
    private function updateCustomerTier($customer)
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
            Log::info("Customer tier updated after VNPay payment for customer {$customer->id}: {$oldTier} → {$newTier}");
        }
    }
}
