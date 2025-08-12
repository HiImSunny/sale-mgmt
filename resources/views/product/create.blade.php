@extends('layouts.app')

@section('title', 'Thêm sản phẩm mới')

@section('page-title', 'Thêm sản phẩm')

@push('styles')
    <style>
        .image-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin: 0.5rem;
        }

        .variant-row {
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            background: var(--bg-white);
        }

        .attribute-select {
            margin-bottom: 0.5rem;
        }

        .remove-variant-btn {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Sản phẩm</a></li>
                        <li class="breadcrumb-item active">Thêm mới</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-1">Thêm sản phẩm mới</h1>
                <p class="text-secondary mb-0">Tạo sản phẩm mới trong hệ thống</p>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
        </div>

        {{-- ✅ ADDED: Alert explaining the logic --}}
        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Lưu ý:</strong>
            <ul class="mb-0 mt-2">
                <li>Nếu <strong>không thêm biến thể</strong>, sản phẩm sẽ được tạo dạng đơn giản với tồn kho được quản lý trực tiếp</li>
                <li>Nếu <strong>có thêm biến thể</strong>, tồn kho sẽ được quản lý ở từng biến thể riêng biệt</li>
            </ul>
        </div>

        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
            @csrf

            <div class="row">
                <!-- Basic Information -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Thông tin cơ bản</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('sku') is-invalid @enderror"
                                           id="sku" name="sku" value="{{ old('sku') }}" required>
                                    @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="category_id" class="form-label">Danh mục <span class="text-danger">*</span></label>
                                    <select class="form-select @error('category_id') is-invalid @enderror"
                                            id="category_id" name="category_id" required>
                                        <option value="">Chọn danh mục</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Trạng thái</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Giá <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('price') is-invalid @enderror"
                                               id="price" name="price" value="{{ old('price') }}" min="0" step="1000" required>
                                        <span class="input-group-text">đ</span>
                                    </div>
                                    @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3" id="stockField">
                                    <label for="stock_quantity" class="form-label">Số lượng tồn kho</label>
                                    <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror"
                                           id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" min="0">
                                    <small class="text-muted">Chỉ áp dụng cho sản phẩm không có biến thể</small>
                                    @error('stock_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Mô tả sản phẩm</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="4">{{ old('description') }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Product Images -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Hình ảnh sản phẩm</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="images" class="form-label">Chọn hình ảnh</label>
                                <input type="file" class="form-control @error('images.*') is-invalid @enderror"
                                       id="images" name="images[]" multiple accept="image/*" onchange="previewImages(this)">
                                <small class="text-muted">Chọn nhiều hình ảnh (JPEG, PNG, JPG, GIF - tối đa 2MB mỗi file)</small>
                                @error('images.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div id="imagePreview" class="d-flex flex-wrap"></div>
                        </div>
                    </div>

                    <!-- Product Variants -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Biến thể sản phẩm</h5>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addVariant()">
                                <i class="fas fa-plus me-2"></i>Thêm biến thể
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="variantsContainer">
                                <p class="text-muted">Nhấp "Thêm biến thể" để tạo các phiên bản khác nhau của sản phẩm</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <div class="card sticky-top">
                        <div class="card-header">
                            <h5 class="mb-0">Thao tác</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Lưu sản phẩm
                                </button>
                                <a href="{{ route('products.index') }}" class="btn btn-light">
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
        let variantIndex = 0;
        const attributes = @json($attributes);

        function previewImages(input) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';

            if (input.files) {
                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'image-preview';
                        preview.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
            }
        }

        // ✅ IMPROVED: Better toggleStockField function
        function toggleStockField() {
            const variantsContainer = document.getElementById('variantsContainer');
            const stockField = document.getElementById('stockField');
            const stockInput = document.getElementById('stock_quantity');
            const stockHelpText = stockField.querySelector('.text-muted');

            const hasVariants = variantsContainer.children.length > 0 && !variantsContainer.querySelector('p');

            if (hasVariants) {
                stockField.style.display = 'none';
                stockInput.value = 0;
                stockInput.disabled = true;
            } else {
                stockField.style.display = 'block';
                stockInput.disabled = false;
                stockHelpText.textContent = 'Chỉ áp dụng cho sản phẩm không có biến thể';
            }
        }

        function addVariant() {
            const container = document.getElementById('variantsContainer');
            const variantHtml = `
        <div class="variant-row position-relative" id="variant-${variantIndex}">
            <button type="button" class="btn btn-danger btn-sm remove-variant-btn" onclick="removeVariant(${variantIndex})">
                <i class="fas fa-times"></i>
            </button>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">SKU biến thể</label>
                    <input type="text" class="form-control" name="variants[${variantIndex}][sku]" placeholder="Tự động tạo nếu để trống">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Giá</label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="variants[${variantIndex}][price]" min="0" step="1000">
                        <span class="input-group-text">đ</span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Số lượng</label>
                    <input type="number" class="form-control" name="variants[${variantIndex}][stock_quantity]" value="0" min="0">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Thuộc tính</label>
                    <div id="attributes-${variantIndex}">
                        ${attributes.map(attr => `
                            <select class="form-select attribute-select" name="variants[${variantIndex}][attribute_values][]">
                                <option value="">${attr.name}</option>
                                ${attr.values.map(value => `<option value="${value.id}">${value.value}</option>`).join('')}
                            </select>
                        `).join('')}
                    </div>
                </div>
            </div>
        </div>
    `;

            if (container.querySelector('p')) {
                container.innerHTML = '';
            }

            container.insertAdjacentHTML('beforeend', variantHtml);
            variantIndex++;

            toggleStockField();
        }

        function removeVariant(index) {
            document.getElementById(`variant-${index}`).remove();

            const container = document.getElementById('variantsContainer');
            if (container.children.length === 0) {
                container.innerHTML = '<p class="text-muted">Nhấp "Thêm biến thể" để tạo các phiên bản khác nhau của sản phẩm</p>';
            }

            toggleStockField();
        }

        // ✅ ADDED: Form validation before submit
        document.getElementById('productForm').addEventListener('submit', function(e) {
            const variantsContainer = document.getElementById('variantsContainer');
            const hasVariants = variantsContainer.children.length > 0 && !variantsContainer.querySelector('p');

            if (hasVariants) {
                // Check if all variants have at least one attribute selected
                const variants = variantsContainer.querySelectorAll('.variant-row');
                let hasValidVariant = false;

                variants.forEach(variant => {
                    const selects = variant.querySelectorAll('.attribute-select');
                    let hasAttribute = false;

                    selects.forEach(select => {
                        if (select.value) {
                            hasAttribute = true;
                        }
                    });

                    if (hasAttribute) {
                        hasValidVariant = true;
                    }
                });

                if (!hasValidVariant) {
                    e.preventDefault();
                    alert('Vui lòng chọn ít nhất một thuộc tính cho từng biến thể hoặc xóa hết biến thể để tạo sản phẩm đơn giản.');
                    return false;
                }
            }
        });
    </script>
@endsection
