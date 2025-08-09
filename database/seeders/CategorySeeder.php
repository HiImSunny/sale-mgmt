<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Thời trang', 'slug' => 'thoi-trang'],
            ['name' => 'Điện tử', 'slug' => 'dien-tu'],
            ['name' => 'Gia dụng', 'slug' => 'gia-dung'],
            ['name' => 'Mỹ phẩm', 'slug' => 'my-pham'],
            ['name' => 'Thực phẩm & Đồ uống', 'slug' => 'thuc-pham-do-uong'],
            ['name' => 'Sách', 'slug' => 'sach'],
            ['name' => 'Văn phòng phẩm', 'slug' => 'van-phong-pham'],
            ['name' => 'Thể thao', 'slug' => 'the-thao'],
            ['name' => 'Mẹ & Bé', 'slug' => 'me-va-be'],
            ['name' => 'Thú cưng', 'slug' => 'thu-cung'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Tạo subcategories cho Thời trang
        $fashionCategory = Category::where('slug', 'thoi-trang')->first();
        Category::create([
            'name' => 'Áo nam',
            'slug' => 'ao-nam',
            'parent_id' => $fashionCategory->id
        ]);
        
        Category::create([
            'name' => 'Quần nam',
            'slug' => 'quan-nam',
            'parent_id' => $fashionCategory->id
        ]);
    }
}
