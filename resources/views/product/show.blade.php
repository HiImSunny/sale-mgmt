@extends('layouts.app')

@section('title', 'Chi tiết sản phẩm - ' . $product->name)
@section('page-title', 'Chi tiết sản phẩm')

@push('styles')
    <style>
        .product-image-gallery img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
        }

        .product-thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.2s;
        }

        .product-thumbnail.active {
            border-color: #0d6efd;
        }

        .price-display {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .sale-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #dc3545;
        }

        .original-price {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 1.2rem;
        }

        .discount-badge {
            background: #dc3545;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .barcode-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }

        .barcode-display {
            font-family: 'Courier New', monospace;
            font-size: 1.2rem;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 10px 0;
        }

        .code-item {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .variant-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.2s;
        }

        .variant-card:hover {
            border-color: #0d6efd;
            box-shadow: 0 2px 4px rgba(13, 110, 253, 0.1);
        }

        .attribute-badge {
            background: #e9ecef;
            color: #495057;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-right: 5px;
            margin-bottom: 5px;
            display: inline-block;
        }

        @media print {
            .no-print { display: none !important; }
            .barcode-section {
                border: 2px solid #000;
                page-break-inside: avoid;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <h1 class="h3 mb-1">{{ $product->name }}</h1>
                <p class="text-secondary mb-0">Chi tiết thông tin sản phẩm</p>
            </div>
            <div class="d-flex gap-2">
                @if (Auth::user()->role === '')
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Chỉnh sửa
                    </a>
                @endif
                <a href="{{ route('products.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Danh sách
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Product Images -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        @if($product->images->count() > 0)
                            <div class="product-image-gallery mb-3">
                                <img id="main-image"
                                     src="{{ asset('images/' . $product->images->first()->path) }}"
                                     alt="{{ $product->name }}"
                                     style="max-width: 500px; max-height: 500px; object-fit: contain;">
                            </div>

                            @if($product->images->count() > 1)
                                <div class="d-flex gap-2 flex-wrap">
                                    @foreach($product->images as $index => $image)
                                        <img src="{{ asset('images/' . $image->path) }}"
                                             alt="{{ $product->name }}"
                                             class="product-thumbnail {{ $index === 0 ? 'active' : '' }}"
                                             onclick="changeMainImage('{{ asset('images/' . $image->path) }}', this)"
                                             style="width: 150px; height: 150px; object-fit: cover; cursor: pointer;">
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Chưa có hình ảnh</p>
                            </div>
                        @endif
                    </div>

                </div>
            </div>

            <!-- Product Info -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Thông tin sản phẩm</h5>
                    </div>
                    <div class="card-body">
                        <!-- Product Name -->
                        <h4 class="mb-3">{{ $product->name }}</h4>

                        <!-- Price -->
                        <div class="price-display mb-3">
                            @if($product->sale_price && $product->sale_price < $product->price)
                                <span class="sale-price">{{ number_format($product->sale_price) }}đ</span>
                                <span class="original-price">{{ number_format($product->price) }}đ</span>
                                <span class="discount-badge">
                                -{{ round((($product->price - $product->sale_price) / $product->price) * 100) }}%
                            </span>
                            @else
                                <span class="fs-3 fw-bold text-primary">{{ number_format($product->price) }}đ</span>
                            @endif
                        </div>

                        <!-- Basic Info -->
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Trạng thái:</strong></div>
                            <div class="col-sm-8">
                                @if($product->status === 1)
                                    <span class="badge bg-success">Hoạt động</span>
                                @else
                                    <span class="badge bg-secondary">Không hoạt động</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Danh mục:</strong></div>
                            <div class="col-sm-8">
                                @if($product->categories && $product->categories->count() > 0)
                                    @foreach($product->categories as $category)
                                        <span class="badge bg-light text-dark me-1">{{ $category->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">Chưa phân loại</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Tồn kho:</strong></div>
                            <div class="col-sm-8">
                                @if($product->has_variants)
                                    <span class="badge bg-primary">{{ $product->total_stock }} từ {{ $product->variants->count() }} biến thể</span>
                                @else
                                    <span class="badge {{ $product->stock_quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $product->stock_quantity }}
                                </span>
                                @endif
                            </div>
                        </div>

                        <!-- Description -->
                        @if($product->description)
                            <div class="mb-3">
                                <strong>Mô tả:</strong>
                                <p class="mt-2">{{ $product->description }}</p>
                            </div>
                        @endif

                        <!-- Timestamps -->
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Ngày tạo:</strong></div>
                            <div class="col-sm-8">{{ $product->created_at->format('d/m/Y H:i') }}</div>
                        </div>

                        <div class="row">
                            <div class="col-sm-4"><strong>Cập nhật:</strong></div>
                            <div class="col-sm-8">{{ $product->updated_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Codes & Barcode -->
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Mã sản phẩm & Mã vạch</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Product Codes -->
                            <div class="col-md-6">
                                <h6 class="mb-3">Mã định danh sản phẩm</h6>

                                <div class="code-item">
                                    <label class="fw-bold">SKU (Stock Keeping Unit):</label>
                                    <div class="barcode-display">{{ $product->sku }}</div>
                                </div>

                                @if($product->ean13)
                                    <div class="code-item">
                                        <label class="fw-bold">EAN13:</label>
                                        <div class="barcode-display">{{ $product->ean13 }}</div>
                                    </div>
                                @endif

                                @if($product->upc)
                                    <div class="code-item">
                                        <label class="fw-bold">UPC:</label>
                                        <div class="barcode-display">{{ $product->upc }}</div>
                                    </div>
                                @endif
                            </div>

                            <!-- Barcode Display -->
                            <div class="col-md-6">
                                <h6 class="mb-3">Mã vạch để quét</h6>
                                <div class="barcode-section" id="barcode-section">
                                    <div class="mb-2">
                                        <strong>{{ $product->name }}</strong>
                                    </div>
                                    <svg id="barcode" class="mb-3"></svg>
                                    <div class="barcode-display">{{ $product->sku }}</div>
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-success me-2" onclick="showBarcode('{{ $product->sku }}', '{{ $product->name }}')">
                                            <i class="fas fa-print me-2"></i>In mã vạch
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Variants -->
        @if($product->variants->count() > 0)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Biến thể sản phẩm ({{ $product->variants->count() }})</h5>
                    <a href="{{ route('product-variants.index', ['product' => $product->id]) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Quản lý biến thể
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($product->variants as $variant)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="variant-card">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <code class="fw-bold">{{ $variant->sku }}</code>
                                        <span class="badge {{ $variant->stock_quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $variant->stock_quantity }}
                                    </span>
                                    </div>

                                    <!-- Variant Codes -->
                                    <div class="mb-2">
                                        @if($variant->ean13)
                                            <small class="text-muted d-block">EAN13: {{ $variant->ean13 }}</small>
                                        @endif
                                        @if($variant->upc)
                                            <small class="text-muted d-block">UPC: {{ $variant->upc }}</small>
                                        @endif
                                    </div>

                                    <!-- Price -->
                                    <div class="mb-2">
                                        @if($variant->sale_price && $variant->sale_price < $variant->price)
                                            <span class="fw-bold text-danger">{{ number_format($variant->sale_price) }}đ</span>
                                            <span class="text-decoration-line-through text-muted ms-1">{{ number_format($variant->price) }}đ</span>
                                        @else
                                            <span class="fw-bold text-primary">{{ number_format($variant->price) }}đ</span>
                                        @endif
                                    </div>

                                    <!-- Attributes -->
                                    <div class="mb-2">
                                        @foreach($variant->attributeValues as $attributeValue)
                                            <span class="attribute-badge">
                                            {{ $attributeValue->attributeValue->attribute->name }}:
                                            {{ $attributeValue->attributeValue->value }}
                                        </span>
                                        @endforeach
                                    </div>

                                    <!-- Variant Actions -->
                                    <div class="d-flex gap-1 mt-2">
                                        <button type="button" class="btn btn-outline-success btn-sm"
                                                onclick="showBarcode('{{ $variant->sku }}', '{{ $variant->product->name }}')">
                                            <i class="fas fa-barcode"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="{{ asset('js/barcode-printer.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Generate main barcode
            try {
                JsBarcode("#barcode", "{{ $product->sku }}", {
                    format: "CODE128",
                    width: 2,
                    height: 100,
                    displayValue: false,
                    margin: 10,
                    fontSize: 14
                });
            } catch (e) {
                document.getElementById('barcode').innerHTML = '<div class="text-danger">Không thể tạo mã vạch</div>';
            }
        });

        function changeMainImage(src, thumbnail) {
            document.getElementById('main-image').src = src;

            // Update active thumbnail
            document.querySelectorAll('.product-thumbnail').forEach(img => {
                img.classList.remove('active');
            });
            thumbnail.classList.add('active');
        }

        function showBarcode(sku, productName) {
            BarcodePrinter.print({
                sku: sku,
                name: productName,
            }, {
                quantity: 20,
                size: 'small',
                showModal: true
            });
        }
    </script>
@endsection
