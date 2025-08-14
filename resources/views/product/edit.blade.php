@extends('layouts.app')

@section('title', 'Chỉnh sửa sản phẩm - ' . $product->name)
@section('page-title', 'Chỉnh sửa sản phẩm')

@push('styles')
    <style>
        .product-form {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .existing-images {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .existing-image-item {
            position: relative;
            width: 150px;
            height: 150px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #dee2e6;
        }

        .existing-image-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .existing-image-item .remove-existing-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(220, 53, 69, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .existing-image-item .set-primary {
            position: absolute;
            bottom: 5px;
            left: 5px;
            background: rgba(13, 110, 253, 0.8);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 10px;
            cursor: pointer;
        }

        .existing-image-item.primary-image {
            border-color: #0d6efd;
            border-width: 3px;
        }

        .existing-image-item.primary-image::after {
            content: 'Ảnh chính';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(13, 110, 253, 0.9);
            color: white;
            text-align: center;
            font-size: 10px;
            padding: 2px;
        }

        .image-upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            transition: border-color 0.3s ease;
            cursor: pointer;
        }

        .image-upload-area:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }

        .new-image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .new-image-preview-item {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #28a745;
        }

        .new-image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .new-image-preview-item .remove-new-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(220, 53, 69, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .new-image-preview-item::after {
            content: 'Mới';
            position: absolute;
            top: 5px;
            left: 5px;
            background: rgba(40, 167, 69, 0.8);
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 10px;
        }

        .variant-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .variant-item {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            position: relative;
        }

        .existing-variant {
            border-left: 4px solid #0d6efd;
        }

        .variant-item .variant-status {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.8rem;
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

        .code-input-group {
            position: relative;
        }

        .code-input-group .form-control {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }

        .barcode-preview {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .change-summary {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .change-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .change-item:last-child {
            border-bottom: none;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Chỉnh sửa sản phẩm</h1>
                <p class="text-secondary mb-0">Cập nhật thông tin sản phẩm: <code>{{ $product->sku }}</code></p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('products.show', $product) }}" class="btn btn-info">
                    <i class="fas fa-eye me-2"></i>Xem sản phẩm
                </a>
                <a href="{{ route('products.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Danh sách
                </a>
            </div>
        </div>

        <!-- Change Summary -->
        <div id="change-summary" class="change-summary" style="display: none;">
            <h6><i class="fas fa-info-circle me-2"></i>Tóm tắt thay đổi</h6>
            <div id="change-list"></div>
        </div>

        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" id="product-form">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Main Form -->
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="card product-form mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Thông tin cơ bản</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="name" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $product->name) }}" required
                                           data-original="{{ $product->name }}">
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="status" class="form-label">Trạng thái</label>
                                    <select class="form-select" id="status" name="status" data-original="{{ $product->status }}">
                                        <option value="1" {{ old('status', $product->status) == 1 ? 'selected' : '' }}>Hoạt động</option>
                                        <option value="0" {{ old('status', $product->status) == 0 ? 'selected' : '' }}>Không hoạt động</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Mô tả sản phẩm</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="4"
                                          data-original="{{ $product->description }}">{{ old('description', $product->description) }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="category_id" class="form-label">Danh mục <span class="text-danger">*</span></label>
                                    <select class="form-select @error('category_id') is-invalid @enderror"
                                            id="category_id" name="category_id[]" multiple required>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ $product->categories->contains($category->id) ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="help-text">Hiện tại: {{ $product->categories->pluck('name')->join(', ') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Codes & Pricing -->
                    <div class="card product-form mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Mã sản phẩm & Giá cả</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                                    <div class="code-input-group">
                                        <input type="text" class="form-control @error('sku') is-invalid @enderror"
                                               id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required
                                               data-original="{{ $product->sku }}">
                                    </div>
                                    @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="ean13" class="form-label">EAN13</label>
                                    <input type="text" class="form-control @error('ean13') is-invalid @enderror"
                                           id="ean13" name="ean13" value="{{ old('ean13', $product->ean13) }}"
                                           data-original="{{ $product->ean13 }}"
                                           maxlength="13">
                                    @error('ean13')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="upc" class="form-label">UPC</label>
                                    <input type="text" class="form-control @error('upc') is-invalid @enderror"
                                           id="upc" name="upc" value="{{ old('upc', $product->upc) }}"
                                           data-original="{{ $product->upc }}"
                                           maxlength="12">
                                    @error('upc')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="price" class="form-label">Giá gốc <span class="text-danger">*</span></label>
                                    <div class="price-input-group">
                                        <input type="number" class="form-control @error('price') is-invalid @enderror"
                                               id="price" name="price" value="{{ old('price', $product->price) }}"
                                               data-original="{{ $product->price }}"
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
                                               data-original="{{ $product->sale_price }}"
                                               min="0" step="1000">
                                        <span class="currency-symbol">đ</span>
                                    </div>
                                    @error('sale_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="stock_quantity" class="form-label">Tồn kho</label>
                                    <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror"
                                           id="stock_quantity" name="stock_quantity"
                                           value="{{ old('stock_quantity', $product->stock_quantity) }}"
                                           data-original="{{ $product->stock_quantity }}"
                                           min="0" {{ $product->has_variants ? 'disabled' : '' }}>
                                    @error('stock_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($product->has_variants)
                                        <div class="help-text">Tự động tính từ biến thể: {{ $product->total_stock }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Images -->
                    <div class="card product-form mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Hình ảnh sản phẩm</h5>
                        </div>
                        <div class="card-body">
                            @if($product->images->count() > 0)
                                <h6>Hình ảnh hiện tại</h6>
                                <div class="existing-images">
                                    @foreach($product->images as $index => $image)
                                        <div class="existing-image-item {{ $index === 0 ? 'primary-image' : '' }}"
                                             data-image-id="{{ $image->id }}">
                                            <img src="{{ Storage::url($image->image_url) }}" alt="{{ $product->name }}">
                                            <button type="button" class="remove-existing-image"
                                                    onclick="removeExistingImage({{ $image->id }})">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            @if($index !== 0)
                                                <button type="button" class="set-primary"
                                                        onclick="setPrimaryImage({{ $image->id }})">
                                                    Đặt làm ảnh chính
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <hr>
                            @endif

                            <h6>Thêm hình ảnh mới</h6>
                            <div class="image-upload-area" onclick="document.getElementById('new_images').click()">
                                <div class="mb-3">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <h6>Thêm hình ảnh mới</h6>
                                    <p class="text-muted mb-0">Kéo thả hoặc click để chọn hình ảnh</p>
                                    <small class="text-muted">Hỗ trợ: JPG, PNG, GIF. Tối đa 2MB mỗi ảnh</small>
                                </div>
                                <input type="file" id="new_images" name="new_images[]" multiple accept="image/*" style="display: none;">
                            </div>
                            <div id="new-image-preview" class="new-image-preview"></div>
                        </div>
                    </div>

                    <!-- Variants Section -->
                    @if($product->has_variants)
                        <div class="card product-form">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Biến thể sản phẩm ({{ $product->variants->count() }})</h5>
                                <a href="{{ route('product-variants.index', $product) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit me-1"></i>Quản lý biến thể
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Sản phẩm này có {{ $product->variants->count() }} biến thể.
                                    Để chỉnh sửa chi tiết từng biến thể, vui lòng sử dụng trang "Quản lý biến thể".
                                </div>

                                <div class="row">
                                    @foreach($product->variants as $variant)
                                        <div class="col-md-6 mb-3">
                                            <div class="variant-item existing-variant">
                                                <div class="variant-status">
                                                <span class="badge {{ $variant->status ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $variant->status ? 'Hoạt động' : 'Tạm dừng' }}
                                                </span>
                                                </div>

                                                <h6><code>{{ $variant->sku }}</code></h6>

                                                <div class="mb-2">
                                                    @if($variant->sale_price && $variant->sale_price < $variant->price)
                                                        <span class="fw-bold text-danger">{{ number_format($variant->sale_price) }}đ</span>
                                                        <span class="text-decoration-line-through text-muted ms-1">{{ number_format($variant->price) }}đ</span>
                                                    @else
                                                        <span class="fw-bold text-primary">{{ number_format($variant->price) }}đ</span>
                                                    @endif
                                                </div>

                                                <div class="mb-2">
                                                <span class="badge {{ $variant->stock_quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                                                    Tồn kho: {{ $variant->stock_quantity }}
                                                </span>
                                                </div>

                                                <div class="small text-muted">
                                                    @foreach($variant->attributeValues as $attrValue)
                                                        <span class="badge bg-light text-dark me-1">
                                                        {{ $attrValue->attributeValue->attribute->name }}:
                                                        {{ $attrValue->attributeValue->value }}
                                                    </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Current Product Info -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Thông tin hiện tại</h5>
                        </div>
                        <div class="card-body text-center">
                            @if($product->images->count() > 0)
                                <img src="{{ Storage::url($product->images->first()->image_url) }}"
                                     alt="{{ $product->name }}"
                                     class="img-fluid rounded mb-3" style="max-height: 200px;">
                            @endif

                            <h6 class="mb-2">{{ $product->name }}</h6>
                            <p class="text-muted mb-2"><code>{{ $product->sku }}</code></p>

                            <div class="mb-3">
                                @if($product->sale_price && $product->sale_price < $product->price)
                                    <span class="fs-4 fw-bold text-danger">{{ number_format($product->sale_price) }}đ</span>
                                    <br>
                                    <span class="text-decoration-line-through text-muted">{{ number_format($product->price) }}đ</span>
                                    <span class="badge bg-danger ms-1">
                                    -{{ round((($product->price - $product->sale_price) / $product->price) * 100) }}%
                                </span>
                                @else
                                    <span class="fs-4 fw-bold text-primary">{{ number_format($product->price) }}đ</span>
                                @endif
                            </div>

                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <div class="h5 mb-0 text-info">{{ $product->has_variants ? $product->total_stock : $product->stock_quantity }}</div>
                                    <small class="text-muted">Tồn kho</small>
                                </div>
                                <div class="col-4">
                                    <div class="h5 mb-0 text-primary">{{ $product->variants->count() }}</div>
                                    <small class="text-muted">Biến thể</small>
                                </div>
                                <div class="col-4">
                                    <div class="h5 mb-0 text-success">{{ $product->images->count() }}</div>
                                    <small class="text-muted">Hình ảnh</small>
                                </div>
                            </div>

                            <!-- Barcode Preview -->
                            <div class="barcode-preview">
                                <h6>Mã vạch hiện tại</h6>
                                <div id="current-barcode"></div>
                                <div class="barcode-text">{{ $product->sku }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Updated Preview -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Xem trước thay đổi</h5>
                        </div>
                        <div class="card-body text-center">
                            <!-- Updated Barcode Preview -->
                            <div class="barcode-preview" id="updated-barcode-preview" style="display: none;">
                                <h6>Mã vạch mới</h6>
                                <div id="updated-barcode"></div>
                                <div class="barcode-text" id="updated-barcode-text"></div>
                            </div>

                            <!-- Updated Price Preview -->
                            <div class="price-preview">
                                <h6>Giá bán mới</h6>
                                <div id="updated-price-display">
                                    <span class="fs-4 fw-bold text-primary" id="display-price">{{ number_format($product->price) }}đ</span>
                                    <div id="sale-price-display" style="display: none;">
                                        <span class="fs-5 fw-bold text-danger" id="display-sale-price">0đ</span>
                                        <span class="text-decoration-line-through text-muted" id="display-original-price">0đ</span>
                                        <span class="badge bg-danger ms-1" id="display-discount">0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card sticky-top">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Cập nhật sản phẩm
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="previewChanges()">
                                    <i class="fas fa-eye me-2"></i>Xem trước
                                </button>
                                <a href="{{ route('products.show', $product) }}" class="btn btn-light">
                                    <i class="fas fa-times me-2"></i>Hủy bỏ
                                </a>
                            </div>

                            <hr>

                            <div class="text-center">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Tạo: {{ $product->created_at->format('d/m/Y H:i') }}<br>
                                    Cập nhật: {{ $product->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Hidden inputs for removed images -->
    <div id="removed-images"></div>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        let removedImageIds = [];
        let newSelectedImages = [];
        let originalData = {};

        document.addEventListener('DOMContentLoaded', function() {
            // Store original data for change tracking
            storeOriginalData();

            // Initialize
            initializeImageUpload();
            initializePricePreview();
            initializeChangeTracking();
            initializeFormValidation();

            // Generate current barcode
            generateCurrentBarcode();
        });

        function storeOriginalData() {
            const inputs = document.querySelectorAll('[data-original]');
            inputs.forEach(input => {
                originalData[input.name] = input.dataset.original;
            });
        }

        function generateCurrentBarcode() {
            try {
                JsBarcode("#current-barcode", "{{ $product->sku }}", {
                    format: "CODE128",
                    width: 1.5,
                    height: 60,
                    displayValue: false,
                    margin: 10
                });
            } catch (e) {
                document.getElementById('current-barcode').innerHTML = '<div class="text-danger">Invalid barcode</div>';
            }
        }

        // Image Upload Functions
        function initializeImageUpload() {
            const newImageInput = document.getElementById('new_images');
            const uploadArea = document.querySelector('.image-upload-area');
            const preview = document.getElementById('new-image-preview');

            newImageInput.addEventListener('change', handleNewImageSelect);

            // Drag and drop
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('drag-over');
            });

            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('drag-over');
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('drag-over');
                const files = e.dataTransfer.files;
                handleNewFiles(files);
            });
        }

        function handleNewImageSelect(e) {
            handleNewFiles(e.target.files);
        }

        function handleNewFiles(files) {
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    newSelectedImages.push(file);
                    addNewImagePreview(file);
                }
            });
            trackChanges();
        }

        function addNewImagePreview(file) {
            const preview = document.getElementById('new-image-preview');
            const reader = new FileReader();

            reader.onload = function(e) {
                const previewItem = document.createElement('div');
                previewItem.className = 'new-image-preview-item';
                previewItem.innerHTML = `
            <img src="${e.target.result}" alt="New Image">
            <button type="button" class="remove-new-image" onclick="removeNewImage(${newSelectedImages.length - 1})">
                <i class="fas fa-times"></i>
            </button>
        `;
                preview.appendChild(previewItem);
            };

            reader.readAsDataURL(file);
        }

        function removeNewImage(index) {
            newSelectedImages.splice(index, 1);
            updateNewImagePreview();
            trackChanges();
        }

        function updateNewImagePreview() {
            const preview = document.getElementById('new-image-preview');
            preview.innerHTML = '';
            newSelectedImages.forEach((file, index) => {
                addNewImagePreview(file);
            });
        }

        function removeExistingImage(imageId) {
            if (confirm('Bạn có chắc chắn muốn xóa hình ảnh này?')) {
                // Add to removed list
                removedImageIds.push(imageId);

                // Hide from UI
                const imageItem = document.querySelector(`[data-image-id="${imageId}"]`);
                imageItem.style.display = 'none';

                // Add hidden input
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'removed_image_ids[]';
                hiddenInput.value = imageId;
                document.getElementById('removed-images').appendChild(hiddenInput);

                trackChanges();
            }
        }

        function setPrimaryImage(imageId) {
            // Remove primary class from all images
            document.querySelectorAll('.existing-image-item').forEach(item => {
                item.classList.remove('primary-image');
            });

            // Add primary class to selected image
            document.querySelector(`[data-image-id="${imageId}"]`).classList.add('primary-image');

            // Add hidden input for primary image
            let primaryInput = document.querySelector('input[name="primary_image_id"]');
            if (!primaryInput) {
                primaryInput = document.createElement('input');
                primaryInput.type = 'hidden';
                primaryInput.name = 'primary_image_id';
                document.getElementById('product-form').appendChild(primaryInput);
            }
            primaryInput.value = imageId;

            trackChanges();
        }

        // Price Preview Functions
        function initializePricePreview() {
            const priceInput = document.getElementById('price');
            const salePriceInput = document.getElementById('sale_price');

            priceInput.addEventListener('input', updatePricePreview);
            salePriceInput.addEventListener('input', updatePricePreview);
        }

        function updatePricePreview() {
            const price = parseFloat(document.getElementById('price').value) || 0;
            const salePrice = parseFloat(document.getElementById('sale_price').value) || 0;

            const displayPrice = document.getElementById('display-price');
            const salePriceDisplay = document.getElementById('sale-price-display');
            const displaySalePrice = document.getElementById('display-sale-price');
            const displayOriginalPrice = document.getElementById('display-original-price');
            const displayDiscount = document.getElementById('display-discount');

            if (salePrice > 0 && salePrice < price) {
                displayPrice.style.display = 'none';
                salePriceDisplay.style.display = 'block';

                displaySalePrice.textContent = formatCurrency(salePrice) + 'đ';
                displayOriginalPrice.textContent = formatCurrency(price) + 'đ';

                const discount = Math.round(((price - salePrice) / price) * 100);
                displayDiscount.textContent = `-${discount}%`;
            } else {
                displayPrice.style.display = 'block';
                salePriceDisplay.style.display = 'none';
                displayPrice.textContent = formatCurrency(price) + 'đ';
            }
        }

        // Barcode Preview Updates
        function updateBarcodePreview() {
            const sku = document.getElementById('sku').value;
            const preview = document.getElementById('updated-barcode-preview');
            const barcodeText = document.getElementById('updated-barcode-text');

            if (sku && sku !== "{{ $product->sku }}") {
                preview.style.display = 'block';
                barcodeText.textContent = sku;

                try {
                    JsBarcode("#updated-barcode", sku, {
                        format: "CODE128",
                        width: 1.5,
                        height: 60,
                        displayValue: false,
                        margin: 10
                    });
                } catch (e) {
                    document.getElementById('updated-barcode').innerHTML = '<div class="text-danger">Invalid barcode</div>';
                }
            } else {
                preview.style.display = 'none';
            }
        }

        document.getElementById('sku').addEventListener('input', updateBarcodePreview);

        // Change Tracking
        function initializeChangeTracking() {
            const inputs = document.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('input', trackChanges);
                input.addEventListener('change', trackChanges);
            });
        }

        function trackChanges() {
            const changes = {};
            const inputs = document.querySelectorAll('[data-original]');

            inputs.forEach(input => {
                const currentValue = input.value;
                const originalValue = input.dataset.original;

                if (currentValue !== originalValue) {
                    changes[input.name] = {
                        from: originalValue,
                        to: currentValue
                    };
                }
            });

            // Check for image changes
            if (removedImageIds.length > 0) {
                changes['removed_images'] = {
                    from: '0 ảnh',
                    to: `${removedImageIds.length} ảnh bị xóa`
                };
            }

            if (newSelectedImages.length > 0) {
                changes['new_images'] = {
                    from: '0 ảnh',
                    to: `${newSelectedImages.length} ảnh mới`
                };
            }

            updateChangeSummary(changes);
        }

        function updateChangeSummary(changes) {
            const summary = document.getElementById('change-summary');
            const changeList = document.getElementById('change-list');

            if (Object.keys(changes).length > 0) {
                summary.style.display = 'block';

                let html = '';
                Object.entries(changes).forEach(([field, change]) => {
                    const fieldName = getFieldDisplayName(field);
                    html += `
                <div class="change-item">
                    <span><strong>${fieldName}:</strong></span>
                    <span>
                        <span class="text-danger">${change.from || 'Trống'}</span>
                        →
                        <span class="text-success">${change.to || 'Trống'}</span>
                    </span>
                </div>
            `;
                });

                changeList.innerHTML = html;
            } else {
                summary.style.display = 'none';
            }
        }

        function getFieldDisplayName(field) {
            const displayNames = {
                'name': 'Tên sản phẩm',
                'sku': 'SKU',
                'ean13': 'EAN13',
                'upc': 'UPC',
                'price': 'Giá gốc',
                'sale_price': 'Giá khuyến mãi',
                'stock_quantity': 'Tồn kho',
                'description': 'Mô tả',
                'status': 'Trạng thái',
                'removed_images': 'Ảnh bị xóa',
                'new_images': 'Ảnh mới'
            };

            return displayNames[field] || field;
        }

        // Form Validation
        function initializeFormValidation() {
            const form = document.getElementById('product-form');
            const priceInput = document.getElementById('price');
            const salePriceInput = document.getElementById('sale_price');

            // Price validation
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

            // Form submit validation
            form.addEventListener('submit', function(e) {
                validatePrices();

                // Update file input with selected images
                if (newSelectedImages.length > 0) {
                    const dt = new DataTransfer();
                    newSelectedImages.forEach(file => dt.items.add(file));
                    document.getElementById('new_images').files = dt.files;
                }
            });
        }

        // Preview Changes
        function previewChanges() {
            trackChanges();
            const changes = document.getElementById('change-list').innerHTML;

            if (changes) {
                const modal = document.createElement('div');
                modal.innerHTML = `
            <div class="modal fade" id="previewModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Xem trước thay đổi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <h6>Các thay đổi sẽ được áp dụng:</h6>
                            ${changes}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('product-form').submit()">
                                Xác nhận cập nhật
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

                document.body.appendChild(modal);
                const bootstrapModal = new bootstrap.Modal(document.getElementById('previewModal'));
                bootstrapModal.show();

                // Remove modal after hiding
                document.getElementById('previewModal').addEventListener('hidden.bs.modal', function() {
                    modal.remove();
                });
            } else {
                alert('Không có thay đổi nào để xem trước');
            }
        }

        // Utility Functions
        function formatCurrency(amount) {
            return parseInt(amount).toLocaleString('vi-VN');
        }
    </script>
@endsection
