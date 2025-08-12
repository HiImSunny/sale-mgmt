@extends('layouts.app')

@section('title', 'Chi tiết sản phẩm - ' . $product->name)

@section('page-title', 'Chi tiết sản phẩm')

@push('styles')
    <style>
        .product-image-main {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 12px;
        }

        .product-image-thumb {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .product-image-thumb:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px var(--shadow-medium);
        }

        .variant-card {
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .variant-card:hover {
            border-color: var(--accent-primary);
            box-shadow: 0 4px 15px var(--shadow-light);
        }

        .attribute-tag {
            background: var(--accent-warm);
            color: var(--accent-primary);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            display: inline-block;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">{{ $product->name }}</h1>
                <p class="text-secondary mb-0">Chi tiết thông tin sản phẩm</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary">
                    <i class="fas fa-edit me-2"></i>Chỉnh sửa
                </a>
                <a href="{{ route('products.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Product Images -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        @if($product->images->count() > 0)
                            <div class="mb-3">
                                <img id="mainImage"
                                     src="{{ Storage::url($product->images->first()->image_url) }}"
                                     alt="{{ $product->name }}"
                                     class="product-image-main">
                            </div>
                            @if($product->images->count() > 1)
                                <div class="d-flex gap-2 flex-wrap">
                                    @foreach($product->images as $image)
                                        <img src="{{ Storage::url($image->image_url) }}"
                                             alt="{{ $product->name }}"
                                             class="product-image-thumb"
                                             onclick="changeMainImage('{{ Storage::url($image->image_url) }}')">
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <div class="product-image-main bg-light d-flex align-items-center justify-content-center">
                                <div class="text-center">
                                    <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Chưa có hình ảnh</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Product Info -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>SKU:</strong></div>
                            <div class="col-sm-8"><code>{{ $product->sku }}</code></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Danh mục:</strong></div>
                            <div class="col-sm-8">
                                @if($product->category)
                                    <span class="badge bg-light text-dark">{{ $product->category->name }}</span>
                                @else
                                    <span class="text-muted">Chưa phân loại</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Giá:</strong></div>
                            <div class="col-sm-8">
                                <h4 class="text-accent mb-0">{{ number_format($product->price) }}đ</h4>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Tồn kho:</strong></div>
                            <div class="col-sm-8">
                                @if($product->has_variants)
                                    <span class="badge {{ $product->total_stock > 0 ? 'bg-success' : 'bg-danger' }}">
                {{ $product->total_stock }} sản phẩm (từ variants)
            </span>
                                @else
                                    <span class="badge {{ $product->stock_quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                {{ $product->stock_quantity }} sản phẩm
            </span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Trạng thái:</strong></div>
                            <div class="col-sm-8">
                            <span class="badge {{ $product->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $product->status === 'active' ? 'Hoạt động' : 'Không hoạt động' }}
                            </span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Ngày tạo:</strong></div>
                            <div class="col-sm-8">
                                {{ $product->created_at->format('d/m/Y H:i') }}
                                <small class="text-muted">({{ $product->created_at->diffForHumans() }})</small>
                            </div>
                        </div>

                        @if($product->description)
                            <div class="mt-4">
                                <h6>Mô tả sản phẩm</h6>
                                <div class="bg-light p-3 rounded">
                                    {!! nl2br(e($product->description)) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Variants -->
        @if($product->variants->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Biến thể sản phẩm ({{ $product->variants->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($product->variants as $variant)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="variant-card">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <code class="text-primary">{{ $variant->sku }}</code>
                                        <span
                                            class="badge {{ $variant->stock_quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $variant->stock_quantity }}
                                    </span>
                                    </div>

                                    <div class="mb-2">
                                        <strong>{{ number_format($variant->price) }}đ</strong>
                                    </div>

                                    @if($variant->attributeValues->count() > 0)
                                        <div>
                                            @foreach($variant->attributeValues as $attributeValue)
                                                <span class="attribute-tag">
                {{ $attributeValue->attributeValue->attribute->name }}:
                {{ $attributeValue->attributeValue->value }}
                                            </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Variant Summary -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="h5 mb-0 text-accent">{{ $product->variants->count() }}</div>
                                <small class="text-muted">Tổng biến thể</small>
                            </div>
                            <div class="col-md-3">
                                <div class="h5 mb-0 text-success">{{ $product->variants->sum('stock_quantity') }}</div>
                                <small class="text-muted">Tổng tồn kho</small>
                            </div>
                            <div class="col-md-3">
                                <div class="h5 mb-0 text-primary">{{ number_format($product->variants->min('price')) }}
                                    đ
                                </div>
                                <small class="text-muted">Giá thấp nhất</small>
                            </div>
                            <div class="col-md-3">
                                <div class="h5 mb-0 text-warning">{{ number_format($product->variants->max('price')) }}
                                    đ
                                </div>
                                <small class="text-muted">Giá cao nhất</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;
        }
    </script>
@endsection
