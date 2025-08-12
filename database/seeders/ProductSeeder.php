<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // Lấy dữ liệu cần thiết với error handling
            $fashionCategory = Category::where('slug', 'thoi-trang')->first();
            if (!$fashionCategory) {
                $this->command->error('Fashion category not found. Please run CategorySeeder first.');
                return;
            }

            $colorAttr = Attribute::where('slug', 'mau-sac')->first();
            $sizeAttr = Attribute::where('slug', 'kich-thuoc')->first();

            if (!$colorAttr || !$sizeAttr) {
                $this->command->error('Attributes not found. Please run AttributeSeeder first.');
                return;
            }

            // Lấy attribute values
            $attributeValues = $this->getAttributeValues($colorAttr, $sizeAttr);

            // Tạo sản phẩm
            $this->createProducts($fashionCategory, $colorAttr, $sizeAttr, $attributeValues);

            DB::commit();
            $this->command->info('Products seeded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error seeding products: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getAttributeValues($colorAttr, $sizeAttr)
    {
        return [
            'colors' => [
                'red' => AttributeValue::where('attribute_id', $colorAttr->id)->where('slug', 'do')->first(),
                'blue' => AttributeValue::where('attribute_id', $colorAttr->id)->where('slug', 'xanh')->first(),
                'black' => AttributeValue::where('attribute_id', $colorAttr->id)->where('slug', 'den')->first(),
                'white' => AttributeValue::where('attribute_id', $colorAttr->id)->where('slug', 'trang')->first(),
                'gray' => AttributeValue::where('attribute_id', $colorAttr->id)->where('slug', 'xam')->first(),
            ],
            'sizes' => [
                's' => AttributeValue::where('attribute_id', $sizeAttr->id)->where('slug', 's')->first(),
                'm' => AttributeValue::where('attribute_id', $sizeAttr->id)->where('slug', 'm')->first(),
                'l' => AttributeValue::where('attribute_id', $sizeAttr->id)->where('slug', 'l')->first(),
                'xl' => AttributeValue::where('attribute_id', $sizeAttr->id)->where('slug', 'xl')->first(),
            ]
        ];
    }

    private function createProducts($fashionCategory, $colorAttr, $sizeAttr, $attributeValues)
    {
        $products = [
            [
                'name' => 'Áo thun nam cổ tròn Premium',
                'slug' => 'ao-thun-nam-co-tron-premium',
                'description' => 'Áo thun nam chất liệu cotton 100% mềm mại, thoáng mát. Form regular fit phù hợp mọi dáng người.',
                'price' => 250000,
                'sale_price' => 199000,
                'sku' => 'ATN001',
                'ean13' => '1234567890123',
                'upc' => '123456789012',
                'stock_quantity' => 0, // Sẽ tính từ variants
                'has_variants' => true,
                'status' => 1,
                'image' => 'products/ao-thun-nam-1.jpg',
                'variants' => [
                    ['color' => 'red', 'size' => 'm', 'stock' => 50, 'price_diff' => 0],
                    ['color' => 'red', 'size' => 'l', 'stock' => 45, 'price_diff' => 0],
                    ['color' => 'red', 'size' => 'xl', 'stock' => 30, 'price_diff' => 10000],
                    ['color' => 'blue', 'size' => 'm', 'stock' => 40, 'price_diff' => 0],
                    ['color' => 'blue', 'size' => 'l', 'stock' => 35, 'price_diff' => 0],
                    ['color' => 'blue', 'size' => 'xl', 'stock' => 25, 'price_diff' => 10000],
                    ['color' => 'black', 'size' => 'm', 'stock' => 60, 'price_diff' => 0],
                    ['color' => 'black', 'size' => 'l', 'stock' => 55, 'price_diff' => 0],
                ]
            ],
            [
                'name' => 'Quần jeans nam Slim Fit',
                'slug' => 'quan-jeans-nam-slim-fit',
                'description' => 'Quần jeans nam form slim fit hiện đại, chất liệu denim cao cấp co giãn nhẹ. Phù hợp đi làm và dạo phố.',
                'price' => 480000,
                'sale_price' => 399000,
                'sku' => 'QJN001',
                'ean13' => '1234567890124',
                'upc' => '123456789013',
                'stock_quantity' => 0,
                'has_variants' => true,
                'status' => 1,
                'image' => 'products/quan-jeans-nam-1.jpg',
                'variants' => [
                    ['color' => 'black', 'size' => 'm', 'stock' => 30, 'price_diff' => 0],
                    ['color' => 'black', 'size' => 'l', 'stock' => 25, 'price_diff' => 0],
                    ['color' => 'black', 'size' => 'xl', 'stock' => 20, 'price_diff' => 20000],
                    ['color' => 'blue', 'size' => 'm', 'stock' => 25, 'price_diff' => 0],
                    ['color' => 'blue', 'size' => 'l', 'stock' => 20, 'price_diff' => 0],
                ]
            ],
            [
                'name' => 'Áo sơ mi nam công sở',
                'slug' => 'ao-so-mi-nam-cong-so',
                'description' => 'Áo sơ mi nam công sở chất liệu cotton pha, chống nhăn, dễ ủi. Thiết kế lịch sự, phù hợp môi trường công sở.',
                'price' => 350000,
                'sale_price' => null,
                'sku' => 'ASM001',
                'ean13' => '1234567890125',
                'upc' => '123456789014',
                'stock_quantity' => 0,
                'has_variants' => true,
                'status' => 1,
                'image' => 'products/ao-so-mi-nam-1.jpg',
                'variants' => [
                    ['color' => 'white', 'size' => 'm', 'stock' => 40, 'price_diff' => 0],
                    ['color' => 'white', 'size' => 'l', 'stock' => 35, 'price_diff' => 0],
                    ['color' => 'white', 'size' => 'xl', 'stock' => 25, 'price_diff' => 15000],
                    ['color' => 'blue', 'size' => 'm', 'stock' => 30, 'price_diff' => 0],
                    ['color' => 'blue', 'size' => 'l', 'stock' => 25, 'price_diff' => 0],
                ]
            ],
            [
                'name' => 'Máy tính bỏ túi Casio',
                'slug' => 'may-tinh-bo-tui-casio',
                'description' => 'Máy tính bỏ túi Casio FX-570VN Plus, chức năng toán học cơ bản và nâng cao.',
                'price' => 450000,
                'sale_price' => null,
                'sku' => 'MTB001',
                'ean13' => '1234567890127',
                'upc' => '123456789016',
                'stock_quantity' => 100, // Sản phẩm không có variants
                'has_variants' => false,
                'status' => 1,
                'image' => 'products/may-tinh-casio-1.jpg',
                'variants' => [] // Không có variants
            ]
        ];

        foreach ($products as $productData) {
            $this->createProduct($productData, $fashionCategory, $colorAttr, $sizeAttr, $attributeValues);
        }
    }

    private function createProduct($data, $fashionCategory, $colorAttr, $sizeAttr, $attributeValues)
    {
        // Tính tổng stock từ variants (nếu có)
        $totalStock = $data['has_variants']
            ? array_sum(array_column($data['variants'], 'stock'))
            : $data['stock_quantity'];

        // Tạo sản phẩm
        $product = Product::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'price' => $data['price'],
            'sale_price' => $data['sale_price'],
            'sku' => $data['sku'],
            'ean13' => $data['ean13'] ?? null,
            'upc' => $data['upc'] ?? null,
            'stock_quantity' => $totalStock,
            'has_variants' => $data['has_variants'],
            'status' => $data['status'],
        ]);

        // Gắn category
        $product->categories()->attach($fashionCategory->id);

        // Thêm ảnh sản phẩm
        ProductImage::create([
            'product_id' => $product->id,
            'path' => $data['image'],
            'sort_order' => 1
        ]);

        // Tạo variants nếu có
        if ($data['has_variants'] && !empty($data['variants'])) {
            $this->createVariants($product, $data['variants'], $colorAttr, $sizeAttr, $attributeValues);
        }

        $variantCount = $data['has_variants'] ? count($data['variants']) : 0;
        $this->command->info("Created product: {$data['name']} with {$variantCount} variants (Total stock: {$totalStock})");
    }

    private function createVariants($product, $variantsData, $colorAttr, $sizeAttr, $attributeValues)
    {
        foreach ($variantsData as $index => $variantData) {
            $colorValue = $attributeValues['colors'][$variantData['color']];
            $sizeValue = $attributeValues['sizes'][$variantData['size']];

            if (!$colorValue || !$sizeValue) {
                $this->command->warn("Skipping variant due to missing attribute values");
                continue;
            }

            // Tạo SKU cho variant
            $variantSku = $product->sku . '-' . strtoupper($variantData['color']) . '-' . strtoupper($variantData['size']);

            // Tạo EAN13 và UPC cho variant (nếu product có)
            $variantEan13 = $product->ean13 ? $product->ean13 . str_pad($index + 1, 2, '0', STR_PAD_LEFT) : null;
            $variantUpc = $product->upc ? $product->upc . str_pad($index + 1, 2, '0', STR_PAD_LEFT) : null;

            // Tính giá variant
            $variantPrice = $product->price + ($variantData['price_diff'] ?? 0);
            $variantSalePrice = $product->sale_price ? ($product->sale_price + ($variantData['price_diff'] ?? 0)) : null;

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $variantSku,
                'ean13' => $variantEan13,
                'upc' => $variantUpc,
                'price' => $variantPrice,
                'sale_price' => $variantSalePrice,
                'stock_quantity' => $variantData['stock'], // Sử dụng stock_quantity thay vì stock
                'status' => 1,
            ]);

            // Gắn attribute values với pivot data
            $variant->attributeValues()->attach([
                $colorValue->id => ['attribute_id' => $colorAttr->id],
                $sizeValue->id => ['attribute_id' => $sizeAttr->id]
            ]);
        }
    }
}
