<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total' => Product::count(),
            'active' => Product::where('status', 1)->count(),
            'inactive' => Product::where('status', 0)->count(),
            'in_stock' => Product::where(function($query) {
                $query->where('has_variants', false)->where('stock_quantity', '>', 0)
                    ->orWhere(function($q) {
                        $q->where('has_variants', true)
                            ->whereHas('variants', function($v) {
                                $v->where('stock_quantity', '>', 0);
                            });
                    });
            })->count(),
            'out_of_stock' => Product::where(function($query) {
                $query->where('has_variants', false)->where('stock_quantity', 0)
                    ->orWhere(function($q) {
                        $q->where('has_variants', true)
                            ->whereDoesntHave('variants', function($v) {
                                $v->where('stock_quantity', '>', 0);
                            });
                    });
            })->count(),
            'with_variants' => Product::whereHas('variants')->withCount('variants')->get()->sum('variants_count'),
        ];

        $query = Product::with(['categories', 'images', 'variants']);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('category') && $request->category) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('categories.id', $request->category);
            });
        }

        if ($request->has('status') && $request->status !== '' && $request->status !== null) {
            $query->where('status', $request->status);
        }

        if ($request->has('stock') && $request->stock !== '') {
            if ($request->stock === 'in_stock') {
                $query->where(function($q) {
                    $q->where('has_variants', false)->where('stock_quantity', '>', 0)
                        ->orWhere(function($subQ) {
                            $subQ->where('has_variants', true)
                                ->whereHas('variants', function($v) {
                                    $v->where('stock_quantity', '>', 0);
                                });
                        });
                });
            } elseif ($request->stock === 'out_of_stock') {
                $query->where(function($q) {
                    $q->where('has_variants', false)->where('stock_quantity', 0)
                        ->orWhere(function($subQ) {
                            $subQ->where('has_variants', true)
                                ->whereDoesntHave('variants', function($v) {
                                    $v->where('stock_quantity', '>', 0);
                                });
                        });
                });
            }
        }

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        switch ($sort) {
            case 'name':
                $query->orderBy('name', $direction);
                break;
            case 'price':
                $query->orderBy('price', $direction);
                break;
            case 'stock':
                $query->addSelect([
                    'total_stock' => function($subQuery) {
                        $subQuery->selectRaw('
                        CASE
                            WHEN products.has_variants = 1
                            THEN COALESCE((
                                SELECT SUM(stock_quantity)
                                FROM product_variants
                                WHERE product_variants.product_id = products.id
                            ), 0)
                            ELSE products.stock_quantity
                        END
                    ');
                    }
                ])->orderBy('total_stock', $direction);
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $direction);
                break;
        }

        $products = $query->paginate(15)->appends(request()->query());
        $categories = Category::all();

        return view('product.index', compact('products', 'categories', 'stats'));
    }

    public function show(Product $product)
    {
        $product->load([
            'categories',
            'images',
            'variants.attributeValues.attributeValue.attribute'
        ]);

        return view('product.show', compact('product'));
    }

    public function create()
    {
        $categories = Category::all();
        $attributes = Attribute::with('values')->get();

        return view('product.create', compact('categories', 'attributes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'stock_quantity' => 'nullable|integer|min:0',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'variants' => 'array',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock_quantity' => 'nullable|integer|min:0',
            'variants.*.attribute_values' => 'array'
        ]);

        // Xác định có variants không
        $hasVariants = $request->has('variants') && is_array($request->variants) && count($request->variants) > 0;

        $product = Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'sku' => $request->sku,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'price' => $request->price,
            'stock_quantity' => $hasVariants ? 0 : ($request->stock_quantity ?? 0),
            'status' => $request->status ?? 'active',
            'has_variants' => $hasVariants
        ]);

        // Handle images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $product->images()->create([
                    'image_url' => $path,
                    'alt_text' => $product->name
                ]);
            }
        }

        // Handle variants
        if ($hasVariants) {
            foreach ($request->variants as $variantData) {
                if (isset($variantData['attribute_values']) && count($variantData['attribute_values']) > 0) {
                    $variant = $product->variants()->create([
                        'sku' => $variantData['sku'] ?? $product->sku . '-' . uniqid(),
                        'price' => $variantData['price'] ?? $product->price,
                        'stock_quantity' => $variantData['stock_quantity'] ?? 0
                    ]);

                    foreach ($variantData['attribute_values'] as $attributeValueId) {
                        $variant->attributeValues()->create([
                            'attribute_value_id' => $attributeValueId
                        ]);
                    }
                }
            }
        }

        return redirect()->route('product.index')->with('success', 'Sản phẩm đã được tạo thành công!');
    }

    public function edit(Product $product)
    {
        $product->load(['images', 'variants.attributeValues.attributeValue.attribute']);
        $categories = Category::all();
        $attributes = Attribute::with('values')->get();

        return view('product.edit', compact('product', 'categories', 'attributes'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'stock_quantity' => 'nullable|integer|min:0',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'sku' => $request->sku,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'price' => $request->price,
            'stock_quantity' => $product->has_variants ? 0 : ($request->stock_quantity ?? 0),
            'status' => $request->status ?? 'active'
        ]);

        // Handle new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $product->images()->create([
                    'image_url' => $path,
                    'alt_text' => $product->name
                ]);
            }
        }

        return redirect()->route('product.show', $product)->with('success', 'Sản phẩm đã được cập nhật thành công!');
    }

    public function destroy(Product $product)
    {
        // Delete images from storage
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_url);
        }

        $product->delete();

        return redirect()->route('product.index')->with('success', 'Sản phẩm đã được xóa thành công!');
    }

    public function deleteImage($productId, $imageId)
    {
        $product = Product::findOrFail($productId);
        $image = $product->images()->findOrFail($imageId);

        Storage::disk('public')->delete($image->image_url);
        $image->delete();

        return response()->json(['success' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id'
        ]);

        $products = Product::whereIn('id', $request->product_ids)->get();

        foreach ($products as $product) {
            // Delete images from storage
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_url);
            }
            $product->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa ' . count($request->product_ids) . ' sản phẩm thành công!'
        ]);
    }

    public function export(Request $request)
    {
        // Determine if exporting selected products or all with filters
        if ($request->has('product_ids')) {
            $products = Product::with(['categories', 'variants'])
                ->whereIn('id', $request->product_ids)
                ->get();
        } else {
            $query = Product::with(['categories', 'variants']);

            // Apply filters
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->has('category') && $request->category) {
                $query->whereHas('categories', function($q) use ($request) {
                    $q->where('categories.id', $request->category);
                });
            }

            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            if ($request->has('stock') && $request->stock !== '') {
                if ($request->stock === 'in_stock') {
                    $query->where(function($q) {
                        $q->where('has_variants', false)->where('stock_quantity', '>', 0)
                            ->orWhere(function($subQ) {
                                $subQ->where('has_variants', true)
                                    ->whereHas('variants', function($v) {
                                        $v->where('stock_quantity', '>', 0);
                                    });
                            });
                    });
                } elseif ($request->stock === 'out_of_stock') {
                    $query->where(function($q) {
                        $q->where('has_variants', false)->where('stock_quantity', 0)
                            ->orWhere(function($subQ) {
                                $subQ->where('has_variants', true)
                                    ->whereDoesntHave('variants', function($v) {
                                        $v->where('stock_quantity', '>', 0);
                                    });
                            });
                    });
                }
            }

            $products = $query->get();
        }

        // Create Excel file
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Danh sách sản phẩm');

        // Headers
        $headers = [
            'A1' => 'ID',
            'B1' => 'Tên sản phẩm',
            'C1' => 'SKU',
            'D1' => 'EAN13',
            'E1' => 'UPC',
            'F1' => 'Danh mục',
            'G1' => 'Giá gốc (VNĐ)',
            'H1' => 'Giá khuyến mãi (VNĐ)',
            'I1' => 'Tồn kho',
            'J1' => 'Có biến thể',
            'K1' => 'Số biến thể',
            'L1' => 'Trạng thái',
            'M1' => 'Ngày tạo'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '0d6efd']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A1:M1')->applyFromArray($headerStyle);

        // Set column widths
        $columnWidths = ['A' => 8, 'B' => 30, 'C' => 15, 'D' => 15, 'E' => 15, 'F' => 20, 'G' => 15, 'H' => 15, 'I' => 12, 'J' => 12, 'K' => 12, 'L' => 15, 'M' => 18];
        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        // Add data
        $row = 2;
        foreach ($products as $product) {
            $totalStock = $product->has_variants ? $product->variants->sum('stock_quantity') : $product->stock_quantity;
            $categoryName = $product->categories->first()->name ?? 'Chưa phân loại';

            $sheet->setCellValue('A' . $row, $product->id);
            $sheet->setCellValue('B' . $row, $product->name);
            $sheet->setCellValue('C' . $row, $product->sku);
            $sheet->setCellValue('D' . $row, $product->ean13 ?? '');
            $sheet->setCellValue('E' . $row, $product->upc ?? '');
            $sheet->setCellValue('F' . $row, $categoryName);
            $sheet->setCellValue('G' . $row, $product->price);
            $sheet->setCellValue('H' . $row, $product->sale_price ?? '');
            $sheet->setCellValue('I' . $row, $totalStock);
            $sheet->setCellValue('J' . $row, $product->has_variants ? 'Có' : 'Không');
            $sheet->setCellValue('K' . $row, $product->variants->count());
            $sheet->setCellValue('L' . $row, $product->status === 1 ? 'Hoạt động' : 'Không hoạt động');
            $sheet->setCellValue('M' . $row, $product->created_at->format('d/m/Y H:i:s'));

            $row++;
        }

        // Create filename and save
        $filename = 'danh_sach_san_pham_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Set proper headers for Excel download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
