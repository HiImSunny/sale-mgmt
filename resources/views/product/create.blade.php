@extends('layouts.app')

@section('title', 'Thêm sản phẩm mới')
@section('page-title', 'Thêm sản phẩm')

@push('styles')
    <style>
        .product-form {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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

        .image-upload-area.drag-over {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }

        .image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .image-preview-item {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #dee2e6;
        }

        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-preview-item .remove-image {
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
        }

        .attribute-selection {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .attribute-option {
            display: inline-block;
        }

        .attribute-option input[type="checkbox"] {
            display: none;
        }

        .attribute-option label {
            display: inline-block;
            padding: 6px 12px;
            background: #fff;
            border: 2px solid #dee2e6;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .attribute-option input[type="checkbox"]:checked + label {
            background: #0d6efd;
            color: white;
            border-color: #0d6efd;
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

        .auto-generate-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #6c757d;
            cursor: pointer;
            font-size: 0.8rem;
        }

        .auto-generate-btn:hover {
            color: #0d6efd;
        }

        .barcode-preview {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .help-text {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Thêm sản phẩm mới</h1>
                <p class="text-secondary mb-0">Tạo sản phẩm mới trong hệ thống</p>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>Danh sách sản phẩm
            </a>
        </div>

        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" id="product-form">
            @csrf

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
                                           id="name" name="name" value="{{ old('name') }}" required
                                           placeholder="Nhập tên sản phẩm">
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="status" class="form-label">Trạng thái</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Hoạt động</option>
                                        <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Không hoạt động</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Mô tả sản phẩm</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="4"
                                          placeholder="Nhập mô tả chi tiết về sản phẩm">{{ old('description') }}</textarea>
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
                                                {{ in_array($category->id, old('category_id', [])) ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="help-text">Có thể chọn nhiều danh mục</div>
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
                                               id="sku" name="sku" value="{{ old('sku') }}" required
                                               placeholder="Nhập SKU hoặc để trống để tự tạo">
                                        <button type="button" class="auto-generate-btn" onclick="generateSKU()" title="Tự động tạo SKU">
                                            <i class="fas fa-magic"></i>
                                        </button>
                                    </div>
                                    @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="help-text">Mã định danh duy nhất cho sản phẩm</div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="ean13" class="form-label">EAN13</label>
                                    <div class="code-input-group">
                                        <input type="text" class="form-control @error('ean13') is-invalid @enderror"
                                               id="ean13" name="ean13" value="{{ old('ean13') }}"
                                               placeholder="13 ký tự" maxlength="13">
                                        <button type="button" class="auto-generate-btn" onclick="generateEAN13()" title="Tự động tạo EAN13">
                                            <i class="fas fa-magic"></i>
                                        </button>
                                    </div>
                                    @error('ean13')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="upc" class="form-label">UPC</label>
                                    <div class="code-input-group">
                                        <input type="text" class="form-control @error('upc') is-invalid @enderror"
                                               id="upc" name="upc" value="{{ old('upc') }}"
                                               placeholder="12 ký tự" maxlength="12">
                                        <button type="button" class="auto-generate-btn" onclick="generateUPC()" title="Tự động tạo UPC">
                                            <i class="fas fa-magic"></i>
                                        </button>
                                    </div>
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
                                               id="price" name="price" value="{{ old('price') }}"
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
                                               id="sale_price" name="sale_price" value="{{ old('sale_price') }}"
                                               min="0" step="1000">
                                        <span class="currency-symbol">đ</span>
                                    </div>
                                    @error('sale_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="help-text">Để trống nếu không có khuyến mãi</div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="stock_quantity" class="form-label">Tồn kho</label>
                                    <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror"
                                           id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', 0) }}"
                                           min="0">
                                    @error('stock_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="help-text">Sẽ được tự động tính nếu có biến thể</div>
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
                            <div class="image-upload-area" onclick="document.getElementById('images').click()">
                                <div class="mb-3">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <h6>Thêm hình ảnh sản phẩm</h6>
                                    <p class="text-muted mb-0">Kéo thả hoặc click để chọn hình ảnh</p>
                                    <small class="text-muted">Hỗ trợ: JPG, PNG, GIF. Tối đa 2MB mỗi ảnh</small>
                                </div>
                                <input type="file" id="images" name="images[]" multiple accept="image/*" style="display: none;">
                            </div>
                            <div id="image-preview" class="image-preview"></div>
                        </div>
                    </div>

                    <!-- Variants Section -->
                    <div class="card product-form">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Biến thể sản phẩm</h5>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="has_variants" name="has_variants"
                                    {{ old('has_variants') ? 'checked' : '' }}>
                                <label class="form-check-label" for="has_variants">Có biến thể</label>
                            </div>
                        </div>
                        <div class="card-body" id="variants-section" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Lưu ý:</strong> Nhấp "Thêm biến thể" để tạo các phiên bản khác nhau của sản phẩm
                                (ví dụ: màu sắc, kích thước khác nhau)
                            </div>

                            <div id="variants-container">
                                <!-- Variants will be added here dynamically -->
                            </div>

                            <button type="button" class="btn btn-outline-primary" onclick="addVariant()">
                                <i class="fas fa-plus me-2"></i>Thêm biến thể
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Preview & Actions -->
                    <div class="card sticky-top">
                        <div class="card-header">
                            <h5 class="mb-0">Xem trước & Thao tác</h5>
                        </div>
                        <div class="card-body">
                            <!-- Barcode Preview -->
                            <div class="barcode-preview mb-3" id="barcode-preview" style="display: none;">
                                <h6>Mã vạch sản phẩm</h6>
                                <div id="barcode"></div>
                                <div class="barcode-text" id="barcode-text"></div>
                            </div>

                            <!-- Price Preview -->
                            <div class="price-preview mb-3">
                                <h6>Giá bán</h6>
                                <div id="price-display">
                                    <span class="fs-4 fw-bold text-primary" id="display-price">0đ</span>
                                    <div id="sale-price-display" style="display: none;">
                                        <span class="fs-5 fw-bold text-danger" id="display-sale-price">0đ</span>
                                        <span class="text-decoration-line-through text-muted" id="display-original-price">0đ</span>
                                        <span class="badge bg-danger ms-1" id="display-discount">0%</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Tạo sản phẩm
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()">
                                    <i class="fas fa-bookmark me-2"></i>Lưu nháp
                                </button>
                                <a href="{{ route('products.index') }}" class="btn btn-light">
                                    <i class="fas fa-times me-2"></i>Hủy bỏ
                                </a>
                            </div>

                            <!-- Help -->
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

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        let variantCount = 0;
        let selectedImages = [];

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize
            initializeImageUpload();
            initializePricePreview();
            initializeVariants();
            initializeFormValidation();

            // Auto-generate SKU from name
            document.getElementById('name').addEventListener('input', function() {
                if (!document.getElementById('sku').value) {
                    generateSKU();
                }
            });
        });

        // Image Upload Functions
        function initializeImageUpload() {
            const imageInput = document.getElementById('images');
            const uploadArea = document.querySelector('.image-upload-area');
            const preview = document.getElementById('image-preview');

            imageInput.addEventListener('change', handleImageSelect);

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
                handleFiles(files);
            });
        }

        function handleImageSelect(e) {
            handleFiles(e.target.files);
        }

        function handleFiles(files) {
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    selectedImages.push(file);
                    addImagePreview(file);
                }
            });
        }

        function addImagePreview(file) {
            const preview = document.getElementById('image-preview');
            const reader = new FileReader();

            reader.onload = function(e) {
                const previewItem = document.createElement('div');
                previewItem.className = 'image-preview-item';
                previewItem.innerHTML = `
            <img src="${e.target.result}" alt="Preview">
            <button type="button" class="remove-image" onclick="removeImage(${selectedImages.length - 1})">
                <i class="fas fa-times"></i>
            </button>
        `;
                preview.appendChild(previewItem);
            };

            reader.readAsDataURL(file);
        }

        function removeImage(index) {
            selectedImages.splice(index, 1);
            updateImagePreview();
        }

        function updateImagePreview() {
            const preview = document.getElementById('image-preview');
            preview.innerHTML = '';
            selectedImages.forEach((file, index) => {
                addImagePreview(file);
            });
        }

        // Price Preview Functions
        function initializePricePreview() {
            const priceInput = document.getElementById('price');
            const salePriceInput = document.getElementById('sale_price');

            priceInput.addEventListener('input', updatePricePreview);
            salePriceInput.addEventListener('input', updatePricePreview);

            updatePricePreview();
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

        // Code Generation Functions
        function generateSKU() {
            const name = document.getElementById('name').value;
            if (name) {
                const sku = name.replace(/[^a-zA-Z0-9]/g, '').substring(0, 10).toUpperCase() +
                    Math.random().toString(36).substring(2, 5).toUpperCase();
                document.getElementById('sku').value = sku;
                updateBarcodePreview();
            }
        }

        function generateEAN13() {
            const ean13 = '123' + Math.random().toString().substring(2, 12);
            document.getElementById('ean13').value = ean13;
        }

        function generateUPC() {
            const upc = Math.random().toString().substring(2, 14);
            document.getElementById('upc').value = upc;
        }

        // Barcode Preview
        function updateBarcodePreview() {
            const sku = document.getElementById('sku').value;
            const preview = document.getElementById('barcode-preview');
            const barcodeText = document.getElementById('barcode-text');

            if (sku) {
                preview.style.display = 'block';
                barcodeText.textContent = sku;

                try {
                    JsBarcode("#barcode", sku, {
                        format: "CODE128",
                        width: 1.5,
                        height: 60,
                        displayValue: false,
                        margin: 10
                    });
                } catch (e) {
                    document.getElementById('barcode').innerHTML = '<div class="text-danger">Invalid barcode</div>';
                }
            } else {
                preview.style.display = 'none';
            }
        }

        document.getElementById('sku').addEventListener('input', updateBarcodePreview);

        // Variants Functions
        function initializeVariants() {
            const hasVariantsCheckbox = document.getElementById('has_variants');
            const variantsSection = document.getElementById('variants-section');
            const stockInput = document.getElementById('stock_quantity');

            hasVariantsCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    variantsSection.style.display = 'block';
                    stockInput.disabled = true;
                    stockInput.value = 0;
                } else {
                    variantsSection.style.display = 'none';
                    stockInput.disabled = false;
                    clearVariants();
                }
            });
        }

        function addVariant() {
            variantCount++;
            const container = document.getElementById('variants-container');

            const variantHtml = `
        <div class="variant-item" id="variant-${variantCount}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Biến thể #${variantCount}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeVariant(${variantCount})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">SKU biến thể</label>
                    <input type="text" class="form-control" name="variants[${variantCount}][sku]"
                           placeholder="Tự động tạo nếu để trống">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">EAN13</label>
                    <input type="text" class="form-control" name="variants[${variantCount}][ean13]"
                           maxlength="13">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">UPC</label>
                    <input type="text" class="form-control" name="variants[${variantCount}][upc]"
                           maxlength="12">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Giá</label>
                    <div class="price-input-group">
                        <input type="number" class="form-control" name="variants[${variantCount}][price]"
                               min="0" step="1000" value="${document.getElementById('price').value}">
                        <span class="currency-symbol">đ</span>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Giá khuyến mãi</label>
                    <div class="price-input-group">
                        <input type="number" class="form-control" name="variants[${variantCount}][sale_price]"
                               min="0" step="1000">
                        <span class="currency-symbol">đ</span>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tồn kho</label>
                    <input type="number" class="form-control" name="variants[${variantCount}][stock_quantity]"
                           min="0" value="0">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Thuộc tính</label>
                <div class="attribute-selection">
                    @foreach($attributes as $attribute)
            <div class="attribute-group mb-2">
                <small class="text-muted">{{ $attribute->name }}:</small>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($attribute->values as $value)
            <div class="attribute-option">
                <input type="checkbox" id="attr_${variantCount}_{{ $attribute->id }}_{{ $value->id }}"
                                               name="variants[${variantCount}][attribute_values][]" value="{{ $value->id }}">
                                        <label for="attr_${variantCount}_{{ $attribute->id }}_{{ $value->id }}">{{ $value->value }}</label>
                                    </div>
                                @endforeach
            </div>
        </div>
@endforeach
            </div>
        </div>
    </div>
`;

            container.insertAdjacentHTML('beforeend', variantHtml);
        }

        function removeVariant(id) {
            document.getElementById(`variant-${id}`).remove();
        }

        function clearVariants() {
            document.getElementById('variants-container').innerHTML = '';
            variantCount = 0;
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
                const dt = new DataTransfer();
                selectedImages.forEach(file => dt.items.add(file));
                document.getElementById('images').files = dt.files;
            });
        }

        // Utility Functions
        function formatCurrency(amount) {
            return parseInt(amount).toLocaleString('vi-VN');
        }

        function saveDraft() {
            const formData = new FormData(document.getElementById('product-form'));
            formData.append('is_draft', '1');

            // Save to localStorage as backup
            const draftData = {};
            for (let [key, value] of formData.entries()) {
                draftData[key] = value;
            }
            localStorage.setItem('product_draft', JSON.stringify(draftData));

            alert('Đã lưu nháp thành công!');
        }

        // Load draft on page load
        window.addEventListener('load', function() {
            const draft = localStorage.getItem('product_draft');
            if (draft) {
                if (confirm('Có bản nháp được lưu trước đó. Bạn có muốn khôi phục không?')) {
                    const draftData = JSON.parse(draft);
                    Object.entries(draftData).forEach(([key, value]) => {
                        const input = document.querySelector(`[name="${key}"]`);
                        if (input && input.type !== 'file') {
                            input.value = value;
                        }
                    });
                    updatePricePreview();
                    updateBarcodePreview();
                } else {
                    localStorage.removeItem('product_draft');
                }
            }
        });

    </script>
@endsection
