<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\AttributeValue;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $fashionCategory = Category::where('slug', 'thoi-trang')->first();
        $colorAttr = Attribute::where('slug', 'mau-sac')->first();
        $sizeAttr = Attribute::where('slug', 'kich-thuoc')->first();
        
        $redColor = AttributeValue::where('attribute_id', $colorAttr->id)
                                 ->where('slug', 'do')->first();
        $blueColor = AttributeValue::where('attribute_id', $colorAttr->id)
                                  ->where('slug', 'xanh')->first();
        $blackColor = AttributeValue::where('attribute_id', $colorAttr->id)
                                   ->where('slug', 'den')->first();
        
        $sizeS = AttributeValue::where('attribute_id', $sizeAttr->id)
                              ->where('slug', 's')->first();
        $sizeM = AttributeValue::where('attribute_id', $sizeAttr->id)
                              ->where('slug', 'm')->first();
        $sizeL = AttributeValue::where('attribute_id', $sizeAttr->id)
                              ->where('slug', 'l')->first();

        // Sản phẩm 1: Áo thun nam
        $product1 = Product::create([
            'name' => 'Áo thun nam cổ tròn',
            'slug' => 'ao-thun-nam-co-tron',
            'description' => 'Áo thun nam chất liệu cotton mềm mại, thoáng mát',
            'price' => 200000,
            'sale_price' => 180000,
            'sku' => 'ATN001',
            'status' => 1,
        ]);

        // Gắn category
        $product1->categories()->attach($fashionCategory->id);

        // Thêm ảnh
        ProductImage::create([
            'product_id' => $product1->id,
            'path' => '/images/products/ao-thun-1.jpg',
            'sort_order' => 1
        ]);

        // Tạo variants với cách attach đúng
        $variants = [
            ['color' => $redColor, 'size' => $sizeM, 'sku' => 'ATN001-DO-M', 'stock' => 50],
            ['color' => $redColor, 'size' => $sizeL, 'sku' => 'ATN001-DO-L', 'stock' => 30],
            ['color' => $blueColor, 'size' => $sizeM, 'sku' => 'ATN001-XANH-M', 'stock' => 40],
            ['color' => $blueColor, 'size' => $sizeL, 'sku' => 'ATN001-XANH-L', 'stock' => 25],
        ];

        foreach ($variants as $variantData) {
            $variant = ProductVariant::create([
                'product_id' => $product1->id,
                'sku' => $variantData['sku'],
                'price' => $product1->price,
                'sale_price' => $product1->sale_price,
                'stock' => $variantData['stock'],
                'status' => 1,
            ]);

            // Sửa cách gắn attributes - cần có attribute_id
            $variant->attributeValues()->attach([
                $variantData['color']->id => ['attribute_id' => $colorAttr->id],
                $variantData['size']->id => ['attribute_id' => $sizeAttr->id]
            ]);
        }

        // Sản phẩm 2: Quần jeans
        $product2 = Product::create([
            'name' => 'Quần jeans nam',
            'slug' => 'quan-jeans-nam',
            'description' => 'Quần jeans nam form slim fit, chất liệu denim cao cấp',
            'price' => 450000,
            'sale_price' => 400000,
            'sku' => 'QJN001',
            'status' => 1,
        ]);

        $product2->categories()->attach($fashionCategory->id);

        ProductImage::create([
            'product_id' => $product2->id,
            'path' => '/images/products/quan-jeans-1.jpg',
            'sort_order' => 1
        ]);

        $jeanVariants = [
            ['color' => $blackColor, 'size' => $sizeM, 'sku' => 'QJN001-DEN-M', 'stock' => 20],
            ['color' => $blackColor, 'size' => $sizeL, 'sku' => 'QJN001-DEN-L', 'stock' => 15],
        ];

        foreach ($jeanVariants as $variantData) {
            $variant = ProductVariant::create([
                'product_id' => $product2->id,
                'sku' => $variantData['sku'],
                'price' => $product2->price,
                'sale_price' => $product2->sale_price,
                'stock' => $variantData['stock'],
                'status' => 1,
            ]);

            // Sửa cách gắn attributes
            $variant->attributeValues()->attach([
                $variantData['color']->id => ['attribute_id' => $colorAttr->id],
                $variantData['size']->id => ['attribute_id' => $sizeAttr->id]
            ]);
        }
    }
}
