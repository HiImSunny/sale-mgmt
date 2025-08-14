@extends('layouts.app')

@section('title', 'Quản lý biến thể - ' . $product->name)
@section('page-title', 'Quản lý biến thể')

@push('styles')
    <style>
        .variant-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
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
        }

        .price-display {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .sale-price {
            font-weight: bold;
            color: #dc3545;
        }

        .original-price {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 0.9em;
        }

        .discount-badge {
            background: #dc3545;
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
        }

        .stock-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .stock-in { background-color: #d1e7dd; color: #0f5132; }
        .stock-low { background-color: #fff3cd; color: #664d03; }
        .stock-out { background-color: #f8d7da; color: #721c24; }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Quản lý biến thể</h1>
                <p class="text-secondary mb-0">{{ $product->name }} - {{ $variants->count() }} biến thể</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('product-variants.create', $product) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Thêm biến thể
                </a>
                <a href="{{ route('products.show', $product) }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại sản phẩm
                </a>
            </div>
        </div>

        <!-- Product Info -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        @if($product->images->count() > 0)
                            <img src="{{ asset('/images/'.$product->images->first()->path) }}"
                                 alt="{{ $product->name }}"
                                 class="img-fluid rounded" style="max-height: 80px;">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 80px;">
                                <i class="fas fa-image text-muted"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-1">{{ $product->name }}</h5>
                        <p class="text-muted mb-1">SKU: <code>{{ $product->sku }}</code></p>
                        <div class="price-display">
                            @if($product->sale_price && $product->sale_price < $product->price)
                                <span class="sale-price">{{ number_format($product->sale_price) }}đ</span>
                                <span class="original-price">{{ number_format($product->price) }}đ</span>
                                <span class="discount-badge">
                                -{{ round((($product->price - $product->sale_price) / $product->price) * 100) }}%
                            </span>
                            @else
                                <span class="fw-bold text-primary">{{ number_format($product->price) }}đ</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="h4 mb-0 text-primary">{{ $variants->count() }}</div>
                                <small class="text-muted">Biến thể</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 mb-0 text-success">{{ $variants->sum('stock_quantity') }}</div>
                                <small class="text-muted">Tổng tồn kho</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 mb-0 text-info">{{ $variants->where('stock_quantity', '>', 0)->count() }}</div>
                                <small class="text-muted">Còn hàng</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Variants List -->
        @if($variants->count() > 0)
            <div class="row">
                @foreach($variants as $variant)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card variant-card h-100">
                            <div class="card-body">
                                <!-- Variant Header -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="mb-1">
                                            <code>{{ $variant->sku }}</code>
                                        </h6>
                                        @if($variant->ean13 || $variant->upc)
                                            <small class="text-muted">
                                                @if($variant->ean13)
                                                    EAN13: {{ $variant->ean13 }}<br>
                                                @endif
                                                @if($variant->upc)
                                                    UPC: {{ $variant->upc }}
                                                @endif
                                            </small>
                                        @endif
                                    </div>
                                    <span class="stock-badge {{ $variant->stock_quantity > 10 ? 'stock-in' : ($variant->stock_quantity > 0 ? 'stock-low' : 'stock-out') }}">
                                    {{ $variant->stock_quantity }}
                                </span>
                                </div>

                                <!-- Price -->
                                <div class="price-display mb-3">
                                    @if($variant->sale_price && $variant->sale_price < $variant->price)
                                        <span class="sale-price">{{ number_format($variant->sale_price) }}đ</span>
                                        <span class="original-price">{{ number_format($variant->price) }}đ</span>
                                        <span class="discount-badge">
                                        -{{ round((($variant->price - $variant->sale_price) / $variant->price) * 100) }}%
                                    </span>
                                    @else
                                        <span class="fw-bold text-primary">{{ number_format($variant->price) }}đ</span>
                                    @endif
                                </div>

                                <!-- Attributes -->
                                <div class="mb-3">
                                    @foreach($variant->attributeValues as $attributeValue)
                                        <span class="attribute-badge">
                                        {{ $attributeValue->attributeValue->attribute->name }}:
                                        {{ $attributeValue->attributeValue->value }}
                                    </span>
                                    @endforeach
                                </div>

                                <!-- Status -->
                                <div class="mb-3">
                                    @if($variant->status === 1)
                                        <span class="badge bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge bg-secondary">Không hoạt động</span>
                                    @endif
                                </div>

                                <!-- Actions -->
                                <div class="d-flex gap-1">
                                    <a href="{{ route('product-variants.edit', $variant) }}"
                                       class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="fas fa-edit"></i> Sửa
                                    </a>
                                    <button type="button" class="btn btn-outline-success btn-sm"
                                            onclick="showVariantBarcode('{{ $variant->sku }}', '{{ addslashes($product->name) }}')"
                                            title="Xem mã vạch">
                                        <i class="fas fa-barcode"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                            onclick="deleteVariant({{ $variant->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-footer text-muted small">
                                Tạo: {{ $variant->created_at->format('d/m/Y') }} •
                                Cập nhật: {{ $variant->updated_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Chưa có biến thể nào</h5>
                    <p class="text-muted mb-4">Thêm biến thể đầu tiên để tạo các phiên bản khác nhau của sản phẩm</p>
                    <a href="{{ route('product-variants.create', $product) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Thêm biến thể đầu tiên
                    </a>
                </div>
            </div>
        @endif
    </div>

    <script src="{{ asset('js/barcode-printer.js') }}"></script>
    <script>
        function showVariantBarcode(sku, productName, price) {
            BarcodePrinter.print({
                sku: sku,
                name: productName,
                price: price
            }, {
                quantity: 20,
                size: 'small',
                showModal: true
            });
        }

        function deleteVariant(variantId) {
            if (confirm('Bạn có chắc chắn muốn xóa biến thể này?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/product-variants/${variantId}`;

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';

                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = '{{ csrf_token() }}';

                form.appendChild(methodInput);
                form.appendChild(tokenInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
@endsection
