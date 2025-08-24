<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\OrderItem;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

class OrderSeeder extends Seeder
{
    public function run(Faker $faker): void
    {
        $variants   = ProductVariant::with('product')->get();
        $customers  = Customer::pluck('id')->all();
        $total      = 300;

        for ($i = 0; $i < $total; $i++) {
            DB::transaction(function () use ($faker, $variants, $customers, $i) {

                // 1. Chọn 1-3 variant cho đơn
                $items = $variants->random(rand(1, 3));

                // 2. Tính tiền
                $subTotal = $items->sum(fn ($v) => $v->price);
                $grandTotal = $subTotal; // chưa có discount

                // 3. 50 % có customer, 50 % không
                $customerId = $i % 2 === 0 ? $faker->randomElement($customers) : null;

                $order = Order::create([
                    'code'           => 'POS' . Str::upper(Str::random(8)),
                    'payment_method' => $faker->randomElement(['vnpay', 'cash_at_counter']),
                    'payment_status' => 'paid',
                    'status'         => 'completed',
                    'subtotal'       => $subTotal,
                    'grand_total'    => $grandTotal,
                    'type'           => 'sale',
                    'customer_id'    => $customerId,
                    'paid_at'        => now(),
                ]);

                foreach ($items as $variant) {
                    OrderItem::create([
                        'order_id'           => $order->id,
                        'product_id'         => $variant->product_id,
                        'product_variant_id' => $variant->id,
                        'name_snapshot'      => $variant->product->name,
                        'sku_snapshot'       => $variant->sku,
                        'unit_price'         => $variant->price,
                        'quantity'           => 1,
                        'line_total'         => $variant->price,
                        'attributes_snapshot'=> json_encode($variant->attributeValues->pluck('value','attribute.slug')),
                    ]);

                    // trừ kho
                    $variant->decrement('stock_quantity', 1);
                }
            });
        }

        $this->command->info("Seeded {$total} orders (50 % có customer_id)");
    }
}
