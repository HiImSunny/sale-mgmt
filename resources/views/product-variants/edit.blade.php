@extends('layouts.app')

@section('title', 'Chỉnh sửa biến thể - ' . $variant->sku)
@section('page-title', 'Chỉnh sửa biến thể')

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

        .current-values {
            background: #e7f1ff;
            border: 2px solid #0d6efd;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 15px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Chỉnh sửa biến thể</h1>
                <p class="text-secondary mb-0">Cập nhật thông tin biến thể: <code>{{ $variant->sku }}</code></p>
            </div>
            <a href="{{ route('product-variants.index', $variant->product) }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
        </div>

        <form action="{{ route('product-variants.update', $variant) }}" method="POST">
            @csrf
            @method('PUT')

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
                                           id="sku" name="sku" value="{{ old('sku', $variant->sku) }}" required>
                                    @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="ean13" class="form-label">EAN13</label>
                                    <input type="text" class="form-control @error('ean13') is-invalid @enderror"
                                           id="ean13" name="ean13" value="{{ old('ean13', $variant->ean13) }}"
                                           placeholder="13 ký tự" maxlength="13">
                                    @error('ean13')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="upc" class="form-label">UPC</label>
                                    <input type="text" class="form-control @error('upc') is-invalid @enderror"
                                           id="upc" name="upc" value="{{ old('upc', $variant->upc) }}"
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
                                               id="price" name="price" value="{{ old('price', $variant->price) }}"
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
                                               id="sale_price" name="sale_price" value="{{ old('sale_price', $variant->sale_price) }}"
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
                                           id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $variant->stock_quantity) }}"
                                           min="0" required>
                                    @error('stock_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Trạng thái</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="1" {{ old('status', $variant->status) == 1 ? 'selected' : '' }}>Hoạt động</option>
                                        <option value="0" {{ old('status', $variant->status) == 0 ? 'selected' : '' }}>Không hoạt động</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attributes Selection -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Thuộc tính biến thể <span class="text-danger">*</span></h5>
                        </div>
                        <div class="card-body">
                            <!-- Current Values Display -->
                            <div class="current-values">
                                <h6 class="mb-2">Thuộc tính hiện tại:</h6>
                                @foreach($variant->attributeValues as $attributeValue)
                                    <span class="badge bg-primary me-2 mb-1">
                                    {{ $attributeValue->attributeValue->attribute->name }}:
                                    {{ $attributeValue->attributeValue->value }}
                                </span>
                                @endforeach
                            </div>

                            @if($attributes->count() > 0)
                                @foreach($attributes as $attribute)
                                    <div class="attribute-group">
                                        <div class="attribute-label">{{ $attribute->name }}</div>
                                        @php
                                            $currentValue = $variant->attributeValues
                                                ->where('attributeValue.attribute_id', $attribute->id)
                                                ->first()
                                                ?->attributeValue
                                                ?->id;
                                        @endphp
                                        @foreach($attribute->values as $value)
                                            <div class="value-option">
                                                <input type="radio"
                                                       id="attr_{{ $attribute->id }}_{{ $value->id }}"
                                                       name="attribute_{{ $attribute->id }}"
                                                       value="{{ $value->id }}"
                                                    {{ $currentValue == $value->id ? 'checked' : '' }}>
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
                                    <p class="text-muted">Tạo thuộc tính trước khi cập nhật biến thể</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Variant Info -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Thông tin biến thể</h5>
                        </div>
                        <div class="card-body text-center">
                            <svg id="barcode" class="mb-3"></svg>
                            <h6 class="mb-2">{{ $variant->sku }}</h6>

                            <div class="mb-3">
                                @if($variant->sale_price && $variant->sale_price < $variant->price)
                                    <span class="fw-bold text-danger fs-5">{{ number_format($variant->sale_price) }}đ</span>
                                    <br>
                                    <span class="text-decoration-line-through text-muted">{{ number_format($variant->price) }}đ</span>
                                    <span class="badge bg-danger ms-1">
                                    -{{ round((($variant->price - $variant->sale_price) / $variant->price) * 100) }}%
                                </span>
                                @else
                                    <span class="fw-bold text-primary fs-5">{{ number_format($variant->price) }}đ</span>
                                @endif
                            </div>

                            <div class="mb-3">
                            <span class="badge {{ $variant->stock_quantity > 10 ? 'bg-success' : ($variant->stock_quantity > 0 ? 'bg-warning' : 'bg-danger') }}">
                                Tồn kho: {{ $variant->stock_quantity }}
                            </span>
                            </div>

                            <button type="button" class="btn btn-outline-success btn-sm" onclick="showBarcode({{ $variant->sku }}, {{ $variant->product->name }})">
                                <i class="fas fa-print me-1"></i>In mã vạch
                            </button>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Sản phẩm gốc</h5>
                        </div>
                        <div class="card-body">
                            @if($variant->product->images->count() > 0)
                                <img src="{{ '/images/'.$variant->product->images->first()->path }}"
                                     alt="{{ $variant->product->name }}"
                                     class="img-fluid rounded mb-3">
                            @endif

                            <h6 class="mb-2">{{ $variant->product->name }}</h6>
                            <p class="text-muted mb-2">SKU: <code>{{ $variant->product->sku }}</code></p>

                            <div class="mb-3">
                                <small class="text-muted">
                                    Tổng biến thể: {{ $variant->product->variants->count() }}<br>
                                    Tồn kho tổng: {{ $variant->product->total_stock }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card sticky-top">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Cập nhật biến thể
                                </button>
                                <a href="{{ route('product-variants.index', $variant->product) }}" class="btn btn-light">
                                    <i class="fas fa-times me-2"></i>Hủy bỏ
                                </a>
                            </div>

                            <hr>

                            <div class="text-center">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Tạo: {{ $variant->created_at->format('d/m/Y H:i') }}<br>
                                    Cập nhật: {{ $variant->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="{{ asset('js/barcode-printer.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Generate barcode
            try {
                JsBarcode("#barcode", "{{ $variant->sku }}", {
                    format: "CODE128",
                    width: 1.5,
                    height: 60,
                    displayValue: false,
                    margin: 5
                });
            } catch (e) {
                document.getElementById('barcode').innerHTML = '<div class="text-danger">Không thể tạo mã vạch</div>';
            }

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
                checkedAttributes.forEach(input => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'attribute_values[]';
                    hiddenInput.value = input.value;
                    this.appendChild(hiddenInput);
                });
            });
        });

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
