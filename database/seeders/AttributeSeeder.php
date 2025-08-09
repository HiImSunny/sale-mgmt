<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Attribute;
use App\Models\AttributeValue;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        // Màu sắc
        $colorAttr = Attribute::create([
            'name' => 'Màu sắc',
            'slug' => 'mau-sac'
        ]);

        $colors = ['Đỏ', 'Xanh', 'Đen', 'Trắng', 'Vàng', 'Xanh lá'];
        foreach ($colors as $color) {
            AttributeValue::create([
                'attribute_id' => $colorAttr->id,
                'value' => $color,
                'slug' => Str::slug($color)
            ]);
        }

        // Kích thước
        $sizeAttr = Attribute::create([
            'name' => 'Kích thước',
            'slug' => 'kich-thuoc'
        ]);

        $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
        foreach ($sizes as $size) {
            AttributeValue::create([
                'attribute_id' => $sizeAttr->id,
                'value' => $size,
                'slug' => strtolower($size)
            ]);
        }

        // Chất liệu
        $materialAttr = Attribute::create([
            'name' => 'Chất liệu',
            'slug' => 'chat-lieu'
        ]);

        $materials = ['Cotton', 'Polyester', 'Da', 'Len', 'Lụa'];
        foreach ($materials as $material) {
            AttributeValue::create([
                'attribute_id' => $materialAttr->id,
                'value' => $material,
                'slug' => Str::slug($material)
            ]);
        }
    }
}
