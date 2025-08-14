@extends('layouts.app')

@section('title', 'Thêm biến thể - ' . $product->name)
@section('page-title', 'Thêm biến thể')

@push('styles')
    <style>
        .attribute-group {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .attribute-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }

        .value-option {
            display: inline-block;
            margin: 5px;
        }

        .value-option input[type="radio"] {
            display: none;
        }

        .value-option label {
            display: inline-block;
            padding: 8px 15px;
            background: #fff;
            border: 2px solid #dee2e6;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .value-option input[type="radio"]:checked + label {
            background: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        .value-option label:hover {
            border-color: #0d6efd;
            background: #e7f1ff;
        }

        .price-input-group {
            position: relative;
        }

        .currency-symbol {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-weight: bold;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Thêm biến thể mới</h1>
                <p class="text-secondary mb-0">Tạo biến thể cho: {{ $product->name }}</p>
            </div>
            <a href="{{ route('product-variants.index', $product) }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
        </div>

        <form action="{{ route('product-variants.store', $product) }}" method="POST">
            @csrf

            <div class="row">
                <!-- Form Fields -->
                <div class="col-md-8">
                    <!-- Basic Info -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Thông tin cơ bản</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('sku') is-invalid @enderror"
                                           id="sku" name="sku" value="{{ old('sku') }}" required
                                           placeholder="Nhập SKU hoặc để trống để tự tạo">
                                    @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Để trống để tự động tạo từ sản phẩm gốc và thuộc tính</small>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="ean13" class="form-label">EAN13</label>
                                    <input type="text" class="form-control @error('ean13') is-invalid @enderror"
                                           id="ean13" name="ean13" value="{{ old('ean13') }}"
                                           placeholder="13 ký tự" maxlength="13">
                                    @error('ean13')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="upc" class="form-label">UPC</label>
                                    <input type="text" class="form-control @error('upc') is-invalid @enderror"
                                           id="upc" name="upc" value="{{ old('upc') }}"
                                           placeholder="12 ký tự" maxlength="12">
                                    @error('upc')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="price" class="form-label">Giá <span class="text-danger">*</span></label>
                                    <div class="price-input-group">
                                        <input type="number" class="form-control @error('price') is-invalid @enderror"
                                               id="price" name="price" value="{{ old('price', $product->price) }}"
                                               min="0" step="1000" required>
                                        <span class="currency-symbol">đ</span>
                                    </div>
                                    @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="sale_price" class="form-label">Giá khuyến mãi</label>
                                    <div class="price-input-group">
                                        <input type="number" class="form-control @error('sale_price') is-invalid @enderror"
                                               id="sale_price" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}"
                                               min="0" step="1000">
                                        <span class="currency-symbol">đ</span>
                                    </div>
                                    @error('sale_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Để trống nếu không có khuyến mãi</small>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="stock_quantity" class="form-label">Tồn kho <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror"
                                           id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', 0) }}"
                                           min="0" required>
                                    @error('stock_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attributes Selection -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Chọn thuộc tính <span class="text-danger">*</span></h5>
                            <small class="text-muted">Chọn ít nhất một thuộc tính để tạo biến thể</small>
                        </div>
                        <div class="card-body">
                            @if($attributes->count() > 0)
                                @foreach($attributes as $attribute)
                                    <div class="attribute-group">
                                        <div class="attribute-label">{{ $attribute->name }}</div>
                                        @foreach($attribute->values as $value)
                                            <div class="value-option">
                                                <input type="radio"
                                                       id="attr_{{ $attribute->id }}_{{ $value->id }}"
                                                       name="attribute_{{ $attribute->id }}"
                                                       value="{{ $value->id }}"
                                                    {{ in_array($value->id, old('attribute_values', [])) ? 'checked' : '' }}>
                                                <label for="attr_{{ $attribute->id }}_{{ $value->id }}">
                                                    {{ $value->value }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach

                                @error('attribute_values')
                                <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-tags fa-2x text-muted mb-3"></i>
                                    <h6 class="text-muted">Chưa có thuộc tính nào</h6>
                                    <p class="text-muted">Tạo thuộc tính trước khi thêm biến thể</p>
                                    <a href="{{ route('attributes.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Tạo thuộc tính
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <div class="card sticky-top">
                        <div class="card-header">
                            <h5 class="mb-0">Sản phẩm gốc</h5>
                        </div>
                        <div class="card-body">
                            @if($product->images->count() > 0)
                                <img src="{{ Storage::url($product->images->first()->image_url) }}"
                                     alt="{{ $product->name }}"
                                     class="img-fluid rounded mb-3">
                            @endif

                            <h6 class="mb-2">{{ $product->name }}</h6>
                            <p class="text-muted mb-2">SKU: <code>{{ $product->sku }}</code></p>

                            <div class="mb-3">
                                @if($product->sale_price && $product->sale_price < $product->price)
                                    <span class="fw-bold text-danger">{{ number_format($product->sale_price) }}đ</span>
                                    <span class="text-decoration-line-through text-muted ms-1">{{ number_format($product->price) }}đ</span>
                                @else
                                    <span class="fw-bold text-primary">{{ number_format($product->price) }}đ</span>
                                @endif
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">Tồn kho tổng: {{ $product->total_stock }}</small>
                            </div>

                            <hr>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Tạo biến thể
                                </button>
                                <a href="{{ route('product-variants.index', $product) }}" class="btn btn-light">
                                    <i class="fas fa-times me-2"></i>Hủy bỏ
                                </a>
                            </div>

                            <hr>

                            <div class="text-center">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Các trường có dấu <span class="text-danger">*</span> là bắt buộc
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto generate SKU based on selected attributes
            const attributeInputs = document.querySelectorAll('input[name^="attribute_"]');
            const skuInput = document.getElementById('sku');
            const baseSku = '{{ $product->sku }}';

            function updateSKU() {
                if (skuInput.value.trim() !== '' && !skuInput.hasAttribute('data-auto')) {
                    return; // Don't override user input
                }

                const selectedValues = [];
                attributeInputs.forEach(input => {
                    if (input.checked) {
                        const label = document.querySelector(`label[for="${input.id}"]`);
                        if (label) {
                            selectedValues.push(label.textContent.trim().substring(0, 3).toUpperCase());
                        }
                    }
                });

                if (selectedValues.length > 0) {
                    skuInput.value = baseSku + '-' + selectedValues.join('-');
                    skuInput.setAttribute('data-auto', 'true');
                } else {
                    skuInput.value = '';
                    skuInput.removeAttribute('data-auto');
                }
            }

            attributeInputs.forEach(input => {
                input.addEventListener('change', updateSKU);
            });

            skuInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    this.removeAttribute('data-auto');
                }
            });

            // Price validation
            const priceInput = document.getElementById('price');
            const salePriceInput = document.getElementById('sale_price');

            function validatePrices() {
                const price = parseFloat(priceInput.value) || 0;
                const salePrice = parseFloat(salePriceInput.value) || 0;

                if (salePrice > 0 && salePrice >= price) {
                    salePriceInput.setCustomValidity('Giá khuyến mãi phải nhỏ hơn giá gốc');
                } else {
                    salePriceInput.setCustomValidity('');
                }
            }

            priceInput.addEventListener('input', validatePrices);
            salePriceInput.addEventListener('input', validatePrices);

            // Form validation
            document.querySelector('form').addEventListener('submit', function(e) {
                const checkedAttributes = document.querySelectorAll('input[name^="attribute_"]:checked');

                if (checkedAttributes.length === 0) {
                    e.preventDefault();
                    alert('Vui lòng chọn ít nhất một thuộc tính cho biến thể');
                    return false;
                }

                // Create hidden inputs for selected attributes
                const attributeValuesInput = document.createElement('input');
                attributeValuesInput.type = 'hidden';
                attributeValuesInput.name = 'attribute_values[]';

                checkedAttributes.forEach(input => {
                    const hiddenInput = attributeValuesInput.cloneNode();
                    hiddenInput.value = input.value;
                    this.appendChild(hiddenInput);
                });
            });
        });
    </script>
@endsection
