<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductController extends Controller
{
    public function byCode(Request $request)
    {
        $code = $request->get('code');

        if (!$code) {
            return response()->json([
                'success' => false,
                'message' => 'Mã không được để trống'
            ], 400);
        }

        // Tìm variant trước (ưu tiên cao nhất)
        $variant = ProductVariant::with(['product.images', 'attributeValues.attribute'])
            ->byCode($code)
            ->active()
            ->first();

        if ($variant) {
            // Lấy thông tin thuộc tính
            $attributes = $variant->attributeValues->map(function ($attrValue) {
                return [
                    'attribute_name' => $attrValue->attribute->name,
                    'value' => $attrValue->value
                ];
            });

            return response()->json([
                'success' => true,
                'type' => 'variant',
                'data' => [
                    'id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'name' => $variant->product->name,
                    'variant_name' => $variant->variant_name,
                    'sku' => $variant->sku,
                    'ean13' => $variant->ean13,
                    'upc' => $variant->upc,
                    'price' => $variant->price,
                    'sale_price' => $variant->sale_price,
                    'final_price' => $variant->final_price,
                    'stock' => $variant->stock,
                    'attributes' => $attributes,
                    'thumbnail' => $variant->product->images->first()?->path ?? '/images/no-image.png'
                ]
            ]);
        }

        // Nếu không tìm thấy variant, tìm products
        $product = Product::with(['variants.attributeValues.attribute', 'images'])
            ->byCode($code)
            ->active()
            ->first();

        if ($product) {
            // Nếu products có variants, yêu cầu chọn variant
            if ($product->variants->count() > 0) {
                $variants = $product->variants->map(function ($variant) {
                    $attributes = $variant->attributeValues->map(function ($attrValue) {
                        return [
                            'attribute_name' => $attrValue->attribute->name,
                            'value' => $attrValue->value
                        ];
                    });

                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'price' => $variant->price,
                        'sale_price' => $variant->sale_price,
                        'final_price' => $variant->final_price,
                        'stock' => $variant->stock,
                        'attributes' => $attributes
                    ];
                });

                return response()->json([
                    'success' => true,
                    'type' => 'product_with_variants',
                    'message' => 'Vui lòng chọn biến thể sản phẩm',
                    'data' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'thumbnail' => $product->images->first()?->path ?? '/images/no-image.png',
                        'variants' => $variants
                    ]
                ]);
            }

            // Nếu không có variants, trả về products trực tiếp
            return response()->json([
                'success' => true,
                'type' => 'products',
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'ean13' => $product->ean13,
                    'upc' => $product->upc,
                    'price' => $product->price,
                    'sale_price' => $product->sale_price,
                    'final_price' => $product->final_price,
                    'thumbnail' => $product->images->first()?->path ?? '/images/no-image.png'
                ]
            ]);
        }

        // Không tìm thấy
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy sản phẩm với mã: ' . $code
        ], 404);
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type', 'all'); // all, products, variant
        $category = $request->get('category');
        $limit = $request->get('limit', 20);

        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập ít nhất 2 ký tự'
            ], 400);
        }

        $results = [];

        // Tìm variants trước (ưu tiên cao)
        if ($type === 'all' || $type === 'variant') {
            $variantQuery = ProductVariant::with(['product.images', 'product.categories', 'attributeValues.attribute'])
                ->whereHas('product', function ($q) use ($query, $category) {
                    $q->where('status', 1);

                    // Tìm theo nhiều trường
                    $q->where(function ($subQ) use ($query) {
                        $subQ->where('name', 'LIKE', "%{$query}%")
                            ->orWhere('sku', 'LIKE', "%{$query}%")
                            ->orWhere('ean13', 'LIKE', "%{$query}%")
                            ->orWhere('upc', 'LIKE', "%{$query}%")
                            ->orWhere('description', 'LIKE', "%{$query}%");
                    });

                    // Lọc theo category nếu có
                    if ($category) {
                        $q->whereHas('categories', function ($catQ) use ($category) {
                            $catQ->where('id', $category);
                        });
                    }
                })
                ->orWhere(function ($q) use ($query) {
                    // Tìm trực tiếp trong variant
                    $q->where('sku', 'LIKE', "%{$query}%")
                        ->orWhere('ean13', 'LIKE', "%{$query}%")
                        ->orWhere('upc', 'LIKE', "%{$query}%");
                })
                ->where('status', 1)
                ->orderBy('stock', 'desc') // Ưu tiên có tồn kho
                ->limit($limit);

            $variants = $variantQuery->get();

            foreach ($variants as $variant) {
                $attributes = $variant->attributeValues->map(function ($attrValue) {
                    return $attrValue->attribute->name . ': ' . $attrValue->value;
                })->implode(', ');

                $categories = $variant->product->categories->pluck('name')->implode(', ');

                $results[] = [
                    'type' => 'variant',
                    'id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'name' => $variant->product->name,
                    'variant_name' => $variant->product->name . ($attributes ? " ({$attributes})" : ''),
                    'sku' => $variant->sku,
                    'ean13' => $variant->ean13,
                    'upc' => $variant->upc,
                    'price' => $variant->price,
                    'sale_price' => $variant->sale_price,
                    'final_price' => $variant->final_price,
                    'stock' => $variant->stock,
                    'categories' => $categories,
                    'attributes' => $attributes,
                    'thumbnail' => $variant->product->images->first()?->path ?? '/images/no-image.png'
                ];
            }
        }

        // Tìm products không có variants
        if ($type === 'all' || $type === 'products') {
            $productQuery = Product::with(['images', 'categories'])
                ->where('status', 1)
                ->whereDoesntHave('variants')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('sku', 'LIKE', "%{$query}%")
                        ->orWhere('ean13', 'LIKE', "%{$query}%")
                        ->orWhere('upc', 'LIKE', "%{$query}%")
                        ->orWhere('description', 'LIKE', "%{$query}%");
                });

            if ($category) {
                $productQuery->whereHas('categories', function ($catQ) use ($category) {
                    $catQ->where('id', $category);
                });
            }

            $products = $productQuery->limit($limit - count($results))->get();

            foreach ($products as $product) {
                $categories = $product->categories->pluck('name')->implode(', ');

                $results[] = [
                    'type' => 'products',
                    'id' => $product->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'variant_name' => $product->name,
                    'sku' => $product->sku,
                    'ean13' => $product->ean13,
                    'upc' => $product->upc,
                    'price' => $product->price,
                    'sale_price' => $product->sale_price,
                    'final_price' => $product->final_price,
                    'stock' => 999, // Giả định không giới hạn cho products
                    'categories' => $categories,
                    'attributes' => '',
                    'thumbnail' => $product->images->first()?->path ?? '/images/no-image.png'
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $results,
            'total' => count($results)
        ]);
    }

    // API lấy danh sách categories cho filter
    public function categories()
    {
        $categories = Category::where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}
