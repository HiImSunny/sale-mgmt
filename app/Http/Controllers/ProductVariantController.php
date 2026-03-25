<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Attribute;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function index(Product $product)
    {
        $variants = $product->variants()->with(['attributeValues.attributeValue.attribute'])->get();

        return view('product-variants.index', compact('product', 'variants'));
    }

    public function create(Product $product)
    {
        $attributes = Attribute::with('values')->get();

        return view('product-variants.create', compact('product', 'attributes'));
    }

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'sku' => 'required|string|unique:product_variants,sku',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'stock_quantity' => 'required|integer|min:0',
            'ean13' => 'nullable|string|size:13|unique:product_variants,ean13',
            'upc' => 'nullable|string|size:12|unique:product_variants,upc',
            'attribute_values' => 'required|array|min:1',
            'attribute_values.*' => 'exists:attribute_values,id'
        ]);

        $variant = $product->variants()->create([
            'sku' => $request->sku,
            'price' => $request->price,
            'sale_price' => $request->sale_price,
            'stock_quantity' => $request->stock_quantity,
            'ean13' => $request->ean13,
            'upc' => $request->upc,
            'status' => 1
        ]);

        // Attach attribute values
        foreach ($request->attribute_values as $attributeValueId) {
            $variant->attributeValues()->create([
                'attribute_value_id' => $attributeValueId
            ]);
        }

        // Update product has_variants flag
        if (!$product->has_variants) {
            $product->update(['has_variants' => true]);
        }

        return redirect()->route('product-variants.index', $product)
            ->with('success', 'Biến thể đã được tạo thành công!');
    }

    public function edit(ProductVariant $variant)
    {
        $variant->load(['attributeValues.attributeValue.attribute', 'product']);
        $attributes = Attribute::with('values')->get();

        return view('product-variants.edit', compact('variant', 'attributes'));
    }

    public function update(Request $request, ProductVariant $variant)
    {
        $request->validate([
            'sku' => 'required|string|unique:product_variants,sku,' . $variant->id,
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'stock_quantity' => 'required|integer|min:0',
            'ean13' => 'nullable|string|size:13|unique:product_variants,ean13,' . $variant->id,
            'upc' => 'nullable|string|size:12|unique:product_variants,upc,' . $variant->id,
            'attribute_values' => 'required|array|min:1',
            'attribute_values.*' => 'exists:attribute_values,id'
        ]);

        $variant->update([
            'sku' => $request->sku,
            'price' => $request->price,
            'sale_price' => $request->sale_price,
            'stock_quantity' => $request->stock_quantity,
            'ean13' => $request->ean13,
            'upc' => $request->upc
        ]);

        // Update attribute values
        $variant->attributeValues()->delete();
        foreach ($request->attribute_values as $attributeValueId) {
            $variant->attributeValues()->create([
                'attribute_value_id' => $attributeValueId
            ]);
        }

        return redirect()->route('product-variants.index', $variant->product)
            ->with('success', 'Biến thể đã được cập nhật thành công!');
    }

    public function destroy(ProductVariant $variant)
    {
        $product = $variant->product;
        $variant->delete();

        if ($product->variants()->count() === 0) {
            $product->update(['has_variants' => false]);
        }

        return redirect()->route('product-variants.index', $product)
            ->with('success', 'Biến thể đã được xóa thành công!');
    }
}
