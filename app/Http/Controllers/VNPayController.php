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

            if (!$this->vnpayService->verifyPayment($request)) {
                return $this->returnResponse($source, false, 'Chữ ký không hợp lệ');
            }

            $order = $this->getOrderFromTxnRef($vnp_TxnRef);
            if (!$order) {
                return $this->returnResponse($source, false, 'Không tìm thấy đơn hàng');
            }

            if ($vnp_ResponseCode == '00') {
                DB::transaction(function () use ($order) {
                    $this->processSuccessfulPayment($order);
                });

                return $this->returnResponse($source, true, 'Thanh toán thành công', $order);
            } else {
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
        if (str_starts_with($txnRef, 'POS_')) {
            $parts = explode('_', $txnRef);
            if (count($parts) >= 2) {
                $orderId = $parts[1];
                return Order::find($orderId);
            }
        }

        return null;
    }

//    public function handleIPN(Request $request)
//    {
//        try {
//            $vnp_ResponseCode = $request->get('vnp_ResponseCode');
//            $vnp_TxnRef = $request->get('vnp_TxnRef');
//
//            if (!$this->vnpayService->verifyPayment($request)) {
//                return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
//            }
//
//            $order = $this->getOrderFromTxnRef($vnp_TxnRef);
//
//            if (!$order) {
//                return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
//            }
//
//            if ($vnp_ResponseCode == '00') {
//                if ($order->payment_status !== 'paid') {
//                    DB::transaction(function () use ($order) {
//                        $this->processSuccessfulPayment($order);
//                    });
//                }
//                return response()->json(['RspCode' => '00', 'Message' => 'Success']);
//            } else {
//                $order->update(['payment_status' => 'failed']);
//                return response()->json(['RspCode' => '00', 'Message' => 'Payment failed']);
//            }
//        } catch (Exception $e) {
//            Log::error('VNPAY IPN Error: ' . $e->getMessage());
//            return response()->json(['RspCode' => '99', 'Message' => 'System error']);
//        }
//    }

    private function processSuccessfulPayment(Order $order)
    {
        $order->update([
            'status' => 'completed',
            'payment_status' => 'paid',
            'paid_at' => now()
        ]);

        foreach ($order->items as $item) {
            if ($item->product_variant_id) {
                $variant = $item->productVariant;

                if ($variant) {
                    if ($variant->stock_quantity < $item->quantity) {
                        Log::warning("Insufficient stock for variant {$variant->id}. Available: {$variant->stock_quantity}, Required: {$item->quantity}");
                    }

                    $variant->decrement('stock_quantity', $item->quantity);

                    InventoryTransaction::create([
                        'product_variant_id' => $variant->id,
                        'type' => 'out',
                        'qty' => $item->quantity,
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'user_id' => $order->user_id,
                        'note' => 'Bán hàng VNPay - Đơn #' . $order->code,
                    ]);

                    Log::info("VNPay payment processed - Variant {$variant->id} stock reduced by {$item->quantity} (Order: {$order->code})");
                }
            } elseif ($item->product_id) {
                $product = Product::find($item->product_id);

                if ($product && !$product->has_variants) {
                    if ($product->stock_quantity < $item->quantity) {
                        Log::warning("Insufficient stock for product {$product->id}. Available: {$product->stock_quantity}, Required: {$item->quantity}");
                    }
                    $product->decrement('stock_quantity', $item->quantity);

                    Log::info("VNPay payment processed - Product {$product->id} stock reduced by {$item->quantity} (Order: {$order->code})");
                }
            }
        }

//        if ($order->customer) {
//            $this->updateCustomerStats($order->customer);
//        }
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
//            Log::info("Customer stats updated after VNPay payment for customer {$customer->id}: Orders: {$customer->total_orders}, Spent: {$customer->total_spent}");
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
