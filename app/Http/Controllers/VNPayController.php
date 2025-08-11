<?php
// app/Http/Controllers/VNPayController.php

namespace App\Http\Controllers;

use App\Services\VNPayQRService;
use App\Models\Order;
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
        if ($source == 'pos')
            return view('vnpay.pos-result', compact('success', 'message', 'order'));

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
            $order = $this->vnpayService->getOrderFromTxnRef($vnp_TxnRef);

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
        // Update order status
        $order->update([
            'status' => 'confirmed',
            'payment_status' => 'paid'
        ]);

        // Create inventory transactions to reduce stock
        foreach ($order->orderItems as $item) {
            $variant = $item->productVariant;

            // Reduce inventory
            $variant->decrement('stock_quantity', $item->quantity);

            // Create inventory transaction
            InventoryTransaction::create([
                'product_variant_id' => $variant->id,
                'type' => 'out',
                'qty' => $item->quantity,
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'note' => 'Bán hàng - Đơn #' . $order->code,
            ]);
        }
    }
}
