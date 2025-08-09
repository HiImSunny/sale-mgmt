<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class POSController extends Controller
{
    public function index()
    {
        return view('seller.pos');
    }

    public function createOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash_at_counter,vnpay'
        ]);

        DB::beginTransaction();
        
        try {
            // Tính toán tổng tiền
            $subtotal = 0;
            $orderItems = [];
            
            foreach ($request->items as $item) {
                $variant = ProductVariant::with(['product', 'attributeValues.attribute'])->find($item['variant_id']);
                
                // Kiểm tra tồn kho
                if ($variant->stock < $item['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Sản phẩm {$variant->variant_name} không đủ tồn kho. Còn lại: {$variant->stock}"
                    ], 400);
                }
                
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;
                
                $orderItems[] = [
                    'variant' => $variant,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $lineTotal
                ];
            }

            // Tạo đơn hàng
            $order = Order::create([
                'user_id' => Auth::id(),
                'payment_method' => $request->payment_method,
                'payment_status' => 'unpaid',
                'status' => 'pending',
                'subtotal' => $subtotal,
                'discount_total' => 0,
                'shipping_fee' => 0,
                'grand_total' => $subtotal,
                'notes' => 'Đơn hàng POS tại quầy'
            ]);

            // Tạo order items và attributes snapshot
            foreach ($orderItems as $itemData) {
                $variant = $itemData['variant'];
                
                // Tạo attributes snapshot
                $attributesSnapshot = $variant->attributeValues->map(function($attrValue) {
                    return [
                        'attribute_name' => $attrValue->attribute->name,
                        'value' => $attrValue->value
                    ];
                })->toArray();

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'name_snapshot' => $variant->variant_name,
                    'sku_snapshot' => $variant->sku,
                    'unit_price' => $itemData['unit_price'],
                    'quantity' => $itemData['quantity'],
                    'line_total' => $itemData['line_total'],
                    'attributes_snapshot' => $attributesSnapshot
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đơn hàng đã được tạo thành công',
                'data' => [
                    'order_id' => $order->id,
                    'order_code' => $order->code,
                    'total' => $order->grand_total
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    public function confirmPayment(Request $request, Order $order)
    {
        $request->validate([
            'payment_method' => 'required|in:cash_at_counter,vnpay'
        ]);

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng đã được thanh toán'
            ], 400);
        }

        DB::beginTransaction();
        
        try {
            // Cập nhật trạng thái thanh toán
            $order->update([
                'payment_status' => 'paid',
                'paid_at' => now()
            ]);

            // Chuyển trạng thái đơn hàng thành confirmed và trừ kho
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

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xác nhận thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }

    private function confirmOrderAndReduceStock(Order $order)
    {
        // Chuyển trạng thái đơn hàng
        $order->update(['status' => 'confirmed']);

        // Trừ kho và ghi inventory transaction
        foreach ($order->items as $item) {
            if ($item->product_variant_id) {
                $variant = $item->productVariant;
                
                // Trừ kho
                $variant->decrement('stock', $item->quantity);
                
                // Ghi inventory transaction
                InventoryTransaction::create([
                    'product_variant_id' => $variant->id,
                    'type' => 'out',
                    'qty' => -$item->quantity,
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'user_id' => Auth::id(),
                    'note' => "Bán hàng POS - Đơn {$order->code}"
                ]);
            }
        }
    }
}
