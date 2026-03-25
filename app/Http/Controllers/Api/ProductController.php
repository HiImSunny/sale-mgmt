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

        $variant = ProductVariant::with(['product.images', 'attributeValues.attributeValue.attribute'])
            ->where(function($q) use ($code) {
                $q->where('sku', $code)
                    ->orWhere('sku', 'LIKE', "%{$code}%")
                    ->orWhere('ean13', 'LIKE', "%{$code}%")
                    ->orWhere('upc', 'LIKE', "%{$code}%");
            })
            ->whereHas('product', function($q) {
                $q->where('status', 1);
            })
            ->first();

        if ($variant) {
            $attributes = [];
            if ($variant->attributeValues) {
                $attributes = $variant->attributeValues->map(function ($attrValue) {
                    try {
                        return [
                            'attribute_name' => $attrValue->attributeValue->attribute->name,
                            'value' => $attrValue->attributeValue->value
                        ];
                    } catch (\Exception $e) {
                        \Log::warning('Error processing attribute value: ' . $e->getMessage());
                        return null;
                    }
                })->filter()->values();
            }

            return response()->json([
                'success' => true,
                'type' => 'variant',
                'data' => [
                    'id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'name' => $variant->product->name,
                    'variant_name' => $variant->product->name,
                    'sku' => $variant->sku,
                    'ean13' => $variant->ean13,
                    'upc' => $variant->upc,
                    'price' => $variant->price,
                    'sale_price' => $variant->sale_price,
                    'stock_quantity' => $variant->stock_quantity,
                    'attributes' => $attributes,
                    'thumbnail' => $variant->product->images->first()?->image_url
                        ? asset('storage/' . $variant->product->images->first()->image_url)
                        : '/images/no-image.png'
                ]
            ]);
        }

        $product = Product::with(['variants.attributeValues.attributeValue.attribute', 'images', 'categories'])
            ->where(function($q) use ($code) {
                $q->where('sku', $code)
                    ->orWhere('sku', 'LIKE', "%{$code}%")
                    ->orWhere('ean13', 'LIKE', "%{$code}%")
                    ->orWhere('upc', 'LIKE', "%{$code}%");
            })
            ->where('status', 1)
            ->first();

        if ($product) {
            if ($product->has_variants && $product->variants->count() > 0) {
                $variants = $product->variants->map(function ($variant) {
                    $attributes = [];
                    if ($variant->attributeValues) {
                        $attributes = $variant->attributeValues->map(function ($attrValue) {
                            try {
                                return [
                                    'attribute_name' => $attrValue->attributeValue->attribute->name,
                                    'value' => $attrValue->attributeValue->value
                                ];
                            } catch (\Exception $e) {
                                return null;
                            }
                        })->filter()->values();
                    }

                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'ean13' => $variant->ean13,
                        'upc' => $variant->upc,
                        'price' => $variant->price,
                        'sale_price' => $variant->sale_price,
                        'stock_quantity' => $variant->stock_quantity,
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
                        'ean13' => $product->ean13,
                        'upc' => $product->upc,
                        'thumbnail' => $product->images->first()?->image_url
                            ? asset('storage/' . $product->images->first()->image_url)
                            : '/images/no-image.png',
                        'variants' => $variants
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'type' => 'product',
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'ean13' => $product->ean13,
                    'upc' => $product->upc,
                    'price' => $product->price,
                    'sale_price' => $product->sale_price,
                    'stock_quantity' => $product->stock_quantity,
                    'thumbnail' => $product->images->first()?->image_url
                        ? asset('storage/' . $product->images->first()->image_url)
                        : '/images/no-image.png'
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
        $type = $request->get('type', 'all');
        $category = $request->get('category');
        $limit = $request->get('limit', 20);

        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập ít nhất 2 ký tự'
            ], 400);
        }

        $results = [];

        if ($type === 'all' || $type === 'variant') {
            try {
                $variantQuery = ProductVariant::with(['product.images', 'product.categories', 'attributeValues.attributeValue.attribute'])
                    ->whereHas('product', function ($q) use ($query, $category) {
                        $q->where('status', 1);

                        $q->where(function ($subQ) use ($query) {
                            $subQ->where('name', 'LIKE', "%{$query}%")
                                ->orWhere('sku', 'LIKE', "%{$query}%");
                        });

                        if ($category) {
                            $q->where('category_id', $category);
                        }
                    })
                    ->orWhere(function ($q) use ($query) {
                        $q->where('sku', 'LIKE', "%{$query}%");
                    })
                    ->orderBy('stock_quantity', 'desc')
                    ->limit($limit);

                $variants = $variantQuery->get();

                foreach ($variants as $variant) {
                    $attributes = '';
                    if ($variant->attributeValues) {
                        try {
                            $attributes = $variant->attributeValues->map(function ($attrValue) {
                                return $attrValue->attributeValue->attribute->name . ': ' . $attrValue->attributeValue->value;
                            })->implode(', ');
                        } catch (\Exception $e) {
                            $attributes = '';
                        }
                    }

                    $categoryName = $variant->product->category?->name ?? '';

                    $results[] = [
                        'type' => 'variant',
                        'id' => $variant->id,
                        'product_id' => $variant->product_id,
                        'name' => $variant->product->name,
                        'variant_name' => $variant->product->name . ($attributes ? " ({$attributes})" : ''),
                        'sku' => $variant->sku,
                        'price' => $variant->price,
                        'sale_price' => $variant->sale_price,
                        'stock_quantity' => $variant->stock_quantity,
                        'category' => $categoryName,
                        'attributes' => $attributes,
                        'thumbnail' => $variant->product->images->first()?->image_url
                            ? asset('storage/' . $variant->product->images->first()->image_url)
                            : '/images/no-image.png'
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Error searching variants: ' . $e->getMessage());
            }
        }

        // Tìm products không có variants
        if ($type === 'all' || $type === 'product') {
            try {
                $productQuery = Product::with(['images', 'categories'])
                    ->where('status', 1)
                    ->where('has_variants', false)
                    ->where(function ($q) use ($query) {
                        $q->where('name', 'LIKE', "%{$query}%")
                            ->orWhere('sku', 'LIKE', "%{$query}%");
                    });

                if ($category) {
                    $productQuery->where('category_id', $category);
                }

                $products = $productQuery->orderBy('stock_quantity', 'desc')
                    ->limit($limit - count($results))
                    ->get();

                foreach ($products as $product) {
                    $categoryName = $product->category?->name ?? '';

                    $results[] = [
                        'type' => 'product',
                        'id' => $product->id,
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'variant_name' => $product->name,
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'sale_price' => $product->sale_price,
                        'stock_quantity' => $product->stock_quantity,
                        'category' => $categoryName,
                        'attributes' => '',
                        'thumbnail' => $product->images->first()?->image_url
                            ? asset('storage/' . $product->images->first()->image_url)
                            : '/images/no-image.png'
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Error searching products: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'data' => $results,
            'total' => count($results)
        ]);
    }

    public function categories()
    {
        $categories = Category::orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}
