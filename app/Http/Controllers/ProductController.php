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

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('categories') && $request->categories) {
            $query->where('category_id', $request->categories);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by stock
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

        // Sorting
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
                // Complex sorting for stock considering variants
                $query->selectRaw('
                    product.*,
                    CASE
                        WHEN product.has_variants = 1
                        THEN COALESCE((SELECT SUM(stock_quantity) FROM product_variants WHERE product_id = product.id), 0)
                        ELSE product.stock_quantity
                    END as total_stock
                ')->orderBy('total_stock', $direction);
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
        $query = Product::with(['categories', 'variants']);

        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('categories') && $request->categories) {
            $query->where('category_id', $request->categories);
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

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set sheet title
        $sheet->setTitle('Danh sách sản phẩm');

        // Headers
        $headers = [
            'A1' => 'ID',
            'B1' => 'Tên sản phẩm',
            'C1' => 'SKU',
            'D1' => 'Danh mục',
            'E1' => 'Giá (VNĐ)',
            'F1' => 'Tồn kho',
            'G1' => 'Có biến thể',
            'H1' => 'Số biến thể',
            'I1' => 'Trạng thái',
            'J1' => 'Ngày tạo'
        ];

        // Set headers
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style headers
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'AE8269']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E2DF']
                ]
            ]
        ];

        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

        // Set column widths
        $columnWidths = [
            'A' => 8,  // ID
            'B' => 30, // Tên sản phẩm
            'C' => 15, // SKU
            'D' => 20, // Danh mục
            'E' => 15, // Giá
            'F' => 12, // Tồn kho
            'G' => 12, // Có biến thể
            'H' => 12, // Số biến thể
            'I' => 15, // Trạng thái
            'J' => 18  // Ngày tạo
        ];

        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        // Add data
        $row = 2;
        foreach ($products as $product) {
            $sheet->setCellValue('A' . $row, $product->id);
            $sheet->setCellValue('B' . $row, $product->name);
            $sheet->setCellValue('C' . $row, $product->sku);
            $sheet->setCellValue('D' . $row, $product->categories ? $product->categories->name : 'Chưa phân loại');
            $sheet->setCellValue('E' . $row, number_format($product->price, 0, ',', '.'));
            $sheet->setCellValue('F' . $row, $product->total_stock);
            $sheet->setCellValue('G' . $row, $product->has_variants ? 'Có' : 'Không');
            $sheet->setCellValue('H' . $row, $product->variants->count());
            $sheet->setCellValue('I' . $row, $product->status === 'active' ? 'Hoạt động' : 'Không hoạt động');
            $sheet->setCellValue('J' . $row, $product->created_at->format('d/m/Y H:i:s'));

            $row++;
        }

        // Style data rows
        if ($row > 2) {
            $dataStyle = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E2DF']
                    ]
                ]
            ];

            $sheet->getStyle('A2:J' . ($row - 1))->applyFromArray($dataStyle);

            // Center align for numeric columns
            $numericStyle = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ];

            $sheet->getStyle('A2:A' . ($row - 1))->applyFromArray($numericStyle); // ID
            $sheet->getStyle('F2:H' . ($row - 1))->applyFromArray($numericStyle); // Tồn kho, Có biến thể, Số biến thể

            // Alternate row colors
            for ($i = 2; $i < $row; $i++) {
                if ($i % 2 == 0) {
                    $sheet->getStyle('A' . $i . ':J' . $i)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8F6F4']
                        ]
                    ]);
                }
            }
        }

        // Add summary information
        $summaryRow = $row + 2;
        $sheet->setCellValue('A' . $summaryRow, 'Thống kê:');
        $sheet->setCellValue('A' . ($summaryRow + 1), 'Tổng số sản phẩm: ' . $products->count());
        $sheet->setCellValue('A' . ($summaryRow + 2), 'Sản phẩm hoạt động: ' . $products->where('status', 1)->count());
        $sheet->setCellValue('A' . ($summaryRow + 3), 'Sản phẩm có biến thể: ' . $products->where('has_variants', true)->count());
        $sheet->setCellValue('A' . ($summaryRow + 4), 'Ngày xuất: ' . now()->format('d/m/Y H:i:s'));

        // Style summary
        $summaryStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '5D524E']
            ]
        ];
        $sheet->getStyle('A' . $summaryRow . ':A' . ($summaryRow + 4))->applyFromArray($summaryStyle);

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Create filename
        $filename = 'danh_sach_san_pham_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Create writer and save to temporary file
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'products_export');
        $writer->save($tempFile);

        // Return download response
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}
