<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // Lấy categories và attributes cần thiết
            $fashionCategory = Category::where('slug', 'thoi-trang')->first();
            $electronicsCategory = Category::where('slug', 'dien-tu')->first();
            $householdCategory = Category::where('slug', 'gia-dung')->first();
            $cosmeticsCategory = Category::where('slug', 'my-pham')->first();
            $sportsCategory = Category::where('slug', 'the-thao')->first();

            if (!$fashionCategory) {
                $this->command->error('Categories not found. Please run CategorySeeder first.');
                return;
            }

            $colorAttr = Attribute::where('slug', 'mau-sac')->first();
            $sizeAttr = Attribute::where('slug', 'kich-thuoc')->first();
            $materialAttr = Attribute::where('slug', 'chat-lieu')->first();

            if (!$colorAttr || !$sizeAttr) {
                $this->command->error('Attributes not found. Please run AttributeSeeder first.');
                return;
            }

            // Lấy attribute values
            $attributeValues = $this->getAttributeValues($colorAttr, $sizeAttr, $materialAttr);

            // Tạo 20 sản phẩm cố định
            $this->createFixedProducts($fashionCategory, $electronicsCategory, $householdCategory, $cosmeticsCategory, $sportsCategory, $colorAttr, $sizeAttr, $materialAttr, $attributeValues);

            // Tạo 30 sản phẩm random
            $this->generateRandomProducts(30, [$fashionCategory, $electronicsCategory, $householdCategory, $cosmeticsCategory, $sportsCategory], $colorAttr, $sizeAttr, $attributeValues);

            DB::commit();
            $this->command->info('Products seeded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error seeding products: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getAttributeValues($colorAttr, $sizeAttr, $materialAttr)
    {
        return [
            'colors' => [
                'red' => AttributeValue::where('attribute_id', $colorAttr->id)->where('slug', 'do')->first(),
                'blue' => AttributeValue::where('attribute_id', $colorAttr->id)->where('slug', 'xanh')->first(),
                'black' => AttributeValue::where('attribute_id', $colorAttr->id)->where('slug', 'den')->first(),
                'white' => AttributeValue::where('attribute_id', $colorAttr->id)->where('slug', 'trang')->first(),
                'yellow' => AttributeValue::where('attribute_id', $colorAttr->id)->where('slug', 'vang')->first(),
                'green' => AttributeValue::where('attribute_id', $colorAttr->id)->where('slug', 'xanh-la')->first(),
            ],
            'sizes' => [
                's' => AttributeValue::where('attribute_id', $sizeAttr->id)->where('slug', 's')->first(),
                'm' => AttributeValue::where('attribute_id', $sizeAttr->id)->where('slug', 'm')->first(),
                'l' => AttributeValue::where('attribute_id', $sizeAttr->id)->where('slug', 'l')->first(),
                'xl' => AttributeValue::where('attribute_id', $sizeAttr->id)->where('slug', 'xl')->first(),
                'xxl' => AttributeValue::where('attribute_id', $sizeAttr->id)->where('slug', 'xxl')->first(),
            ],
            'materials' => [
                'cotton' => AttributeValue::where('attribute_id', $materialAttr->id)->where('slug', 'cotton')->first(),
                'polyester' => AttributeValue::where('attribute_id', $materialAttr->id)->where('slug', 'polyester')->first(),
                'leather' => AttributeValue::where('attribute_id', $materialAttr->id)->where('slug', 'da')->first(),
                'wool' => AttributeValue::where('attribute_id', $materialAttr->id)->where('slug', 'len')->first(),
                'silk' => AttributeValue::where('attribute_id', $materialAttr->id)->where('slug', 'lua')->first(),
            ]
        ];
    }

    private function createFixedProducts($fashionCategory, $electronicsCategory, $householdCategory, $cosmeticsCategory, $sportsCategory, $colorAttr, $sizeAttr, $materialAttr, $attributeValues)
    {
        $products = [
            // THỜI TRANG (10 sản phẩm)
            [
                'name' => 'Áo thun nam cổ tròn Premium',
                'slug' => 'ao-thun-nam-co-tron-premium',
                'description' => 'Áo thun nam chất liệu cotton 100% mềm mại, thoáng mát. Form regular fit phù hợp mọi dáng người.',
                'price' => 250000,
                'sale_price' => 199000,
                'sku' => 'ATN001',
                'category' => $fashionCategory,
                'image' => 'products/ao-thun-nam-1.jpg',
                'has_variants' => true,
                'variants' => [
                    ['color' => 'red', 'size' => 'm', 'stock' => 50],
                    ['color' => 'red', 'size' => 'l', 'stock' => 45],
                    ['color' => 'blue', 'size' => 'm', 'stock' => 40],
                    ['color' => 'black', 'size' => 'm', 'stock' => 60],
                    ['color' => 'black', 'size' => 'l', 'stock' => 55],
                ]
            ],
            [
                'name' => 'Quần jeans nam Slim Fit',
                'slug' => 'quan-jeans-nam-slim-fit',
                'description' => 'Quần jeans nam form slim fit hiện đại, chất liệu denim cao cấp co giãn nhẹ.',
                'price' => 480000,
                'sale_price' => 399000,
                'sku' => 'QJN001',
                'category' => $fashionCategory,
                'image' => 'products/quan-jeans-nam-1.jpg',
                'has_variants' => true,
                'variants' => [
                    ['color' => 'black', 'size' => 'm', 'stock' => 30],
                    ['color' => 'black', 'size' => 'l', 'stock' => 25],
                    ['color' => 'blue', 'size' => 'm', 'stock' => 25],
                    ['color' => 'blue', 'size' => 'l', 'stock' => 20],
                ]
            ],
            [
                'name' => 'Áo sơ mi nam công sở',
                'slug' => 'ao-so-mi-nam-cong-so',
                'description' => 'Áo sơ mi nam công sở chất liệu cotton pha, chống nhăn, dễ ủi.',
                'price' => 350000,
                'sale_price' => null,
                'sku' => 'ASM001',
                'category' => $fashionCategory,
                'image' => 'products/ao-so-mi-nam-1.jpg',
                'has_variants' => true,
                'variants' => [
                    ['color' => 'white', 'size' => 'm', 'stock' => 40],
                    ['color' => 'white', 'size' => 'l', 'stock' => 35],
                    ['color' => 'blue', 'size' => 'm', 'stock' => 30],
                ]
            ],
            [
                'name' => 'Áo khoác hoodie unisex',
                'slug' => 'ao-khoac-hoodie-unisex',
                'description' => 'Áo khoác hoodie phong cách streetwear, chất liệu nỉ bông mềm mại.',
                'price' => 450000,
                'sale_price' => 359000,
                'sku' => 'HOD001',
                'category' => $fashionCategory,
                'image' => 'products/hoodie-1.jpg',
                'has_variants' => true,
                'variants' => [
                    ['color' => 'black', 'size' => 'm', 'stock' => 35],
                    ['color' => 'black', 'size' => 'l', 'stock' => 30],
                    ['color' => 'red', 'size' => 'm', 'stock' => 25],
                    ['color' => 'green', 'size' => 'l', 'stock' => 20],
                ]
            ],
            [
                'name' => 'Váy midi nữ thanh lịch',
                'slug' => 'vay-midi-nu-thanh-lich',
                'description' => 'Váy midi nữ thiết kế thanh lịch, phù hợp đi làm và dự tiệc.',
                'price' => 380000,
                'sale_price' => 299000,
                'sku' => 'VMD001',
                'category' => $fashionCategory,
                'image' => 'products/vay-midi-1.jpg',
                'has_variants' => true,
                'variants' => [
                    ['color' => 'black', 'size' => 'm', 'stock' => 30],
                    ['color' => 'blue', 'size' => 'm', 'stock' => 25],
                    ['color' => 'white', 'size' => 'l', 'stock' => 20],
                ]
            ],
            [
                'name' => 'Áo thun nữ basic',
                'slug' => 'ao-thun-nu-basic',
                'description' => 'Áo thun nữ basic dễ phối đồ, chất liệu cotton mềm mại.',
                'price' => 180000,
                'sale_price' => 149000,
                'sku' => 'ATN002',
                'category' => $fashionCategory,
                'image' => 'products/ao-thun-nu-1.jpg',
                'has_variants' => true,
                'variants' => [
                    ['color' => 'white', 'size' => 'm', 'stock' => 45],
                    ['color' => 'black', 'size' => 'm', 'stock' => 40],
                    ['color' => 'yellow', 'size' => 'l', 'stock' => 35],
                ]
            ],
            [
                'name' => 'Quần short nam thể thao',
                'slug' => 'quan-short-nam-the-thao',
                'description' => 'Quần short nam thể thao, chất liệu thấm hút mồ hôi tốt.',
                'price' => 220000,
                'sale_price' => null,
                'sku' => 'QSN001',
                'category' => $sportsCategory,
                'image' => 'products/quan-short-1.jpg',
                'has_variants' => true,
                'variants' => [
                    ['color' => 'black', 'size' => 'm', 'stock' => 35],
                    ['color' => 'blue', 'size' => 'm', 'stock' => 30],
                    ['color' => 'red', 'size' => 'l', 'stock' => 25],
                ]
            ],
            [
                'name' => 'Áo polo nam cao cấp',
                'slug' => 'ao-polo-nam-cao-cap',
                'description' => 'Áo polo nam thiết kế sang trọng, chất liệu pique cotton.',
                'price' => 320000,
                'sale_price' => 259000,
                'sku' => 'APL001',
                'category' => $fashionCategory,
                'image' => 'products/ao-polo-1.jpg',
                'has_variants' => true,
                'variants' => [
                    ['color' => 'white', 'size' => 'm', 'stock' => 40],
                    ['color' => 'blue', 'size' => 'm', 'stock' => 35],
                    ['color' => 'black', 'size' => 'l', 'stock' => 30],
                ]
            ],
            [
                'name' => 'Giày sneaker nam trắng',
                'slug' => 'giay-sneaker-nam-trang',
                'description' => 'Giày sneaker nam màu trắng basic, phù hợp mọi phong cách.',
                'price' => 680000,
                'sale_price' => 599000,
                'sku' => 'GSN001',
                'category' => $fashionCategory,
                'image' => 'products/giay-sneaker-1.jpg',
                'has_variants' => false,
                'stock_quantity' => 50,
            ],
            [
                'name' => 'Túi xách nữ da thật',
                'slug' => 'tui-xach-nu-da-that',
                'description' => 'Túi xách nữ chất liệu da thật cao cấp, thiết kế sang trọng.',
                'price' => 850000,
                'sale_price' => 759000,
                'sku' => 'TXN001',
                'category' => $fashionCategory,
                'image' => 'products/tui-xach-1.jpg',
                'has_variants' => false,
                'stock_quantity' => 25,
            ],

            // ĐIỆN TỬ (5 sản phẩm)
            [
                'name' => 'Tai nghe Bluetooth cao cấp',
                'slug' => 'tai-nghe-bluetooth-cao-cap',
                'description' => 'Tai nghe Bluetooth 5.0, âm thanh stereo chất lượng cao, pin 20h.',
                'price' => 450000,
                'sale_price' => 399000,
                'sku' => 'TNB001',
                'category' => $electronicsCategory,
                'image' => 'products/tai-nghe-1.jpg',
                'has_variants' => false,
                'stock_quantity' => 80,
            ],
            [
                'name' => 'Chuột không dây gaming',
                'slug' => 'chuot-khong-day-gaming',
                'description' => 'Chuột gaming không dây, DPI cao, thiết kế ergonomic.',
                'price' => 350000,
                'sale_price' => null,
                'sku' => 'CKD001',
                'category' => $electronicsCategory,
                'image' => 'products/chuot-gaming-1.jpg',
                'has_variants' => false,
                'stock_quantity' => 60,
            ],
            [
                'name' => 'Loa Bluetooth mini',
                'slug' => 'loa-bluetooth-mini',
                'description' => 'Loa Bluetooth mini di động, bass mạnh, pin 12h.',
                'price' => 280000,
                'sale_price' => 229000,
                'sku' => 'LBM001',
                'category' => $electronicsCategory,
                'image' => 'products/loa-mini-1.jpg',
                'has_variants' => false,
                'stock_quantity' => 45,
            ],
            [
                'name' => 'Bàn phím cơ gaming',
                'slug' => 'ban-phim-co-gaming',
                'description' => 'Bàn phím cơ gaming RGB, switch xanh, độ bền cao.',
                'price' => 1200000,
                'sale_price' => 999000,
                'sku' => 'BPC001',
                'category' => $electronicsCategory,
                'image' => 'products/ban-phim-co-1.jpg',
                'has_variants' => false,
                'stock_quantity' => 35,
            ],
            [
                'name' => 'Webcam HD 1080p',
                'slug' => 'webcam-hd-1080p',
                'description' => 'Webcam HD 1080p cho học online, họp trực tuyến.',
                'price' => 420000,
                'sale_price' => 359000,
                'sku' => 'WCH001',
                'category' => $electronicsCategory,
                'image' => 'products/webcam-1.jpg',
                'has_variants' => false,
                'stock_quantity' => 40,
            ],

            // GIA DỤNG (3 sản phẩm)
            [
                'name' => 'Nồi cơm điện 1.5L',
                'slug' => 'noi-com-dien-1-5l',
                'description' => 'Nồi cơm điện 1.5L cho gia đình 4-6 người, lòng nồi chống dính.',
                'price' => 550000,
                'sale_price' => 479000,
                'sku' => 'NCD001',
                'category' => $householdCategory,
                'image' => 'products/noi-com-1.jpg',
                'has_variants' => false,
                'stock_quantity' => 30,
            ],
            [
                'name' => 'Bình đun siêu tốc 2L',
                'slug' => 'binh-dun-sieu-toc-2l',
                'description' => 'Bình đun siêu tốc inox 2L, tự ngắt khi sôi.',
                'price' => 180000,
                'sale_price' => null,
                'sku' => 'BDS001',
                'category' => $householdCategory,
                'image' => 'products/binh-dun-1.jpg',
                'has_variants' => false,
                'stock_quantity' => 55,
            ],
            [
                'name' => 'Máy xay sinh tố đa năng',
                'slug' => 'may-xay-sinh-to-da-nang',
                'description' => 'Máy xay sinh tố đa năng, công suất 350W, cối xay inox.',
                'price' => 380000,
                'sale_price' => 329000,
                'sku' => 'MXS001',
                'category' => $householdCategory,
                'image' => 'products/may-xay-1.jpg',
                'has_variants' => false,
                'stock_quantity' => 25,
            ],

            // MỸ PHẨM (2 sản phẩm)
            [
                'name' => 'Kem dưỡng da mặt chống lão hoá',
                'slug' => 'kem-duong-da-mat-chong-lao-hoa',
                'description' => 'Kem dưỡng da mặt chống lão hoá với vitamin C và collagen.',
                'price' => 450000,
                'sale_price' => 399000,
                'sku' => 'KDD001',
                'category' => $cosmeticsCategory,
                'image' => 'products/kem-duong-1.jpg',
                'has_variants' => false,
                'stock_quantity' => 60,
            ],
            [
                'name' => 'Son môi lâu trôi 24h',
                'slug' => 'son-moi-lau-troi-24h',
                'description' => 'Son môi lâu trôi 24h, màu sắc tươi tắn, không khô môi.',
                'price' => 280000,
                'sale_price' => 229000,
                'sku' => 'SML001',
                'category' => $cosmeticsCategory,
                'image' => 'products/son-moi-1.jpg',
                'has_variants' => false,
                'stock_quantity' => 80,
            ],
        ];

        foreach ($products as $productData) {
            $this->createProduct($productData, $colorAttr, $sizeAttr, $attributeValues);
        }
    }

    private function generateRandomProducts(int $count, array $categories, $colorAttr, $sizeAttr, $attributeValues): void
    {
        $faker = fake();

        $productTypes = [
            'Áo thun', 'Quần jean', 'Áo sơ mi', 'Váy', 'Áo khoác', 'Quần short',
            'Điện thoại', 'Laptop', 'Tai nghe', 'Chuột', 'Bàn phím', 'Loa',
            'Nồi cơm', 'Bình đun', 'Máy xay', 'Chảo', 'Nồi', 'Ly cốc',
            'Kem dưỡng', 'Son môi', 'Phấn', 'Nước hoa', 'Sữa rửa mặt', 'Mặt nạ',
            'Giày thể thao', 'Túi xách', 'Balo', 'Đồng hồ', 'Kính mắt', 'Mũ'
        ];

        $adjectives = ['cao cấp', 'premium', 'đa năng', 'hiện đại', 'thông minh', 'tiện lợi', 'sang trọng', 'chất lượng'];

        for ($i = 0; $i < $count; $i++) {
            $productType = $faker->randomElement($productTypes);
            $adjective = $faker->randomElement($adjectives);
            $name = $productType . ' ' . $adjective;
            $slug = Str::slug($name) . '-' . $i;
            $sku = 'RND' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            $price = $faker->numberBetween(100000, 1500000);
            $hasSale = $faker->boolean(40);
            $salePrice = $hasSale ? $price - $faker->numberBetween(20000, 200000) : null;
            $category = $faker->randomElement($categories);

            $hasVariants = $faker->boolean(60); // 60% có variants

            $productData = [
                'name' => $name,
                'slug' => $slug,
                'description' => $faker->paragraph(2),
                'price' => $price,
                'sale_price' => $salePrice,
                'sku' => $sku,
                'category' => $category,
                'image' => 'products/dummy-' . ($i + 1) . '.jpg',
                'has_variants' => $hasVariants,
            ];

            if ($hasVariants) {
                // Tạo variants với 2-4 tổ hợp màu-size
                $colors = array_rand($attributeValues['colors'], rand(2, 3));
                $sizes = array_rand($attributeValues['sizes'], rand(2, 3));
                $variants = [];

                foreach ((array)$colors as $color) {
                    foreach ((array)$sizes as $size) {
                        $variants[] = [
                            'color' => $color,
                            'size' => $size,
                            'stock' => $faker->numberBetween(10, 50)
                        ];
                    }
                }
                $productData['variants'] = $variants;
            } else {
                $productData['stock_quantity'] = $faker->numberBetween(20, 100);
            }

            $this->createProduct($productData, $colorAttr, $sizeAttr, $attributeValues);
        }

        $this->command->info("Generated {$count} random products");
    }

    private function createProduct($data, $colorAttr, $sizeAttr, $attributeValues)
    {
        // Tính tổng stock từ variants (nếu có)
        $totalStock = $data['has_variants'] && isset($data['variants'])
            ? array_sum(array_column($data['variants'], 'stock'))
            : ($data['stock_quantity'] ?? 0);

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
            'status' => 1,
        ]);

        // Gắn category
        $product->categories()->attach($data['category']->id);

        // Thêm ảnh sản phẩm
        ProductImage::create([
            'product_id' => $product->id,
            'path' => $data['image'],
            'sort_order' => 1
        ]);

        // Tạo variants nếu có
        if ($data['has_variants'] && isset($data['variants'])) {
            $this->createVariants($product, $data['variants'], $colorAttr, $sizeAttr, $attributeValues);
        }

        $variantCount = $data['has_variants'] && isset($data['variants']) ? count($data['variants']) : 0;
        $this->command->info("Created product: {$data['name']} with {$variantCount} variants (Total stock: {$totalStock})");
    }

    private function createVariants($product, $variantsData, $colorAttr, $sizeAttr, $attributeValues)
    {
        foreach ($variantsData as $index => $variantData) {
            $colorValue = $attributeValues['colors'][$variantData['color']] ?? null;
            $sizeValue = $attributeValues['sizes'][$variantData['size']] ?? null;

            if (!$colorValue || !$sizeValue) {
                $this->command->warn("Skipping variant due to missing attribute values");
                continue;
            }

            // Tạo SKU cho variant
            $variantSku = $product->sku . '-' . strtoupper($variantData['color']) . '-' . strtoupper($variantData['size']);

            // Tạo EAN13 và UPC cho variant (nếu product có)
            $variantEan13 = $product->ean13 ? $product->ean13 . str_pad($index + 1, 2, '0', STR_PAD_LEFT) : null;
            $variantUpc = $product->upc ? $product->upc . str_pad($index + 1, 2, '0', STR_PAD_LEFT) : null;

            // Tính giá variant (có thể thêm phụ phí cho size XL, XXL)
            $priceDiff = in_array($variantData['size'], ['xl', 'xxl']) ? 10000 : 0;
            $variantPrice = $product->price + $priceDiff;
            $variantSalePrice = $product->sale_price ? ($product->sale_price + $priceDiff) : null;

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $variantSku,
                'ean13' => $variantEan13,
                'upc' => $variantUpc,
                'price' => $variantPrice,
                'sale_price' => $variantSalePrice,
                'stock_quantity' => $variantData['stock'],
                'status' => 1,
            ]);

            // Gắn attribute values với pivot data
            $variant->attributeValuesDirect()->attach([
                $colorValue->id => ['attribute_id' => $colorAttr->id],
                $sizeValue->id => ['attribute_id' => $sizeAttr->id]
            ]);
        }
    }
}
