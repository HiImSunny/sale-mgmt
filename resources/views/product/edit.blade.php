@extends('layouts.app')

@section('title', 'Chỉnh sửa sản phẩm - ' . $product->name)

@section('page-title', 'Chỉnh sửa sản phẩm')

@push('styles')
    <style>
        .current-image {
            position: relative;
            display: inline-block;
            margin: 0.5rem;
        }

        .current-image img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }

        .remove-image-btn {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }

        .image-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin: 0.5rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Chỉnh sửa sản phẩm</h1>
                <p class="text-secondary mb-0">Cập nhật thông tin sản phẩm</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('products.show', $product) }}" class="btn btn-light">
                    <i class="fas fa-eye me-2"></i>Xem chi tiết
                </a>
                <a href="{{ route('products.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Danh sách
                </a>
            </div>
        </div>

        {{-- ✅ ADDED: Alert for products with variants --}}
        @if($product->has_variants)
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Lưu ý:</strong> Sản phẩm này có biến thể. Tồn kho được quản lý ở từng biến thể riêng biệt.
                Để chỉnh sửa tồn kho, vui lòng truy cập trang quản lý biến thể.
            </div>
        @endif

        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

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
                                           id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('sku') is-invalid @enderror"
                                           id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required>
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
                                            <option value="{{ $category->id }}"
                                                {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
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
                                        <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Hoạt động</option>
                                        <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Giá <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('price') is-invalid @enderror"
                                               id="price" name="price" value="{{ old('price', $product->price) }}" min="0" step="1000" required>
                                        <span class="input-group-text">đ</span>
                                    </div>
                                    @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- ✅ FIXED: Stock quantity field with has_variants logic --}}
                                <div class="col-md-6 mb-3" id="stockField" style="{{ $product->has_variants ? 'display: none;' : '' }}">
                                    <label for="stock_quantity" class="form-label">Số lượng tồn kho</label>
                                    <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror"
                                           id="stock_quantity" name="stock_quantity"
                                           value="{{ old('stock_quantity', $product->has_variants ? 0 : $product->stock_quantity) }}"
                                           min="0" {{ $product->has_variants ? 'readonly' : '' }}>
                                    <small class="text-muted">
                                        {{ $product->has_variants ? 'Sản phẩm có biến thể - tồn kho được quản lý ở từng biến thể' : 'Chỉ áp dụng cho sản phẩm không có biến thể' }}
                                    </small>
                                    @error('stock_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Mô tả sản phẩm</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
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
                            <!-- Current Images -->
                            @if($product->images->count() > 0)
                                <div class="mb-3">
                                    <label class="form-label">Hình ảnh hiện tại</label>
                                    <div class="d-flex flex-wrap" id="currentImages">
                                        @foreach($product->images as $image)
                                            <div class="current-image" id="image-{{ $image->id }}">
                                                <img src="{{ Storage::url($image->image_url) }}" alt="{{ $product->name }}">
                                                <button type="button" class="btn btn-danger btn-sm remove-image-btn"
                                                        onclick="removeImage({{ $product->id }}, {{ $image->id }})">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Add New Images -->
                            <div class="mb-3">
                                <label for="images" class="form-label">Thêm hình ảnh mới</label>
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

                    <!-- Product Variants (View Only) -->
                    @if($product->variants->count() > 0)
                        <div class="card">
                            {{-- ✅ FIXED: Enhanced header with total stock info --}}
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">Biến thể sản phẩm ({{ $product->variants->count() }})</h5>
                                    <small class="text-muted">Chỉ xem - để chỉnh sửa biến thể, vui lòng sử dụng trang quản lý biến thể riêng</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-primary">{{ $product->total_stock }}</div>
                                    <small class="text-muted">Tổng tồn kho</small>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                        <tr>
                                            <th>SKU</th>
                                            <th>Giá</th>
                                            <th>Tồn kho</th>
                                            <th>Thuộc tính</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($product->variants as $variant)
                                            <tr>
                                                <td><code>{{ $variant->sku }}</code></td>
                                                <td>{{ number_format($variant->price) }}đ</td>
                                                <td>
                                                    <span class="badge {{ $variant->stock_quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $variant->stock_quantity }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @foreach($variant->attributeValues as $attributeValue)
                                                        <span class="badge bg-light text-dark me-1">
                                                            {{ $attributeValue->attributeValue->attribute->name }}:
                                                            {{ $attributeValue->attributeValue->value }}
                                                        </span>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
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
                                    <i class="fas fa-save me-2"></i>Cập nhật sản phẩm
                                </button>
                                <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-eye me-2"></i>Xem chi tiết
                                </a>
                                <a href="{{ route('products.index') }}" class="btn btn-light">
                                    <i class="fas fa-times me-2"></i>Hủy bỏ
                                </a>
                            </div>

                            <hr>

                            {{-- ✅ FIXED: Enhanced product stats with has_variants info --}}
                            <div class="text-center">
                                <div class="row text-center g-3">
                                    <div class="col-4">
                                        <div class="text-accent h5 mb-0">{{ $product->variants->count() }}</div>
                                        <small class="text-muted">Biến thể</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-success h5 mb-0">{{ $product->images->count() }}</div>
                                        <small class="text-muted">Hình ảnh</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="h5 mb-0 {{ $product->has_variants ? 'text-primary' : 'text-secondary' }}">
                                            {{ $product->has_variants ? 'Có' : 'Không' }}
                                        </div>
                                        <small class="text-muted">Có biến thể</small>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="text-center">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Tạo: {{ $product->created_at->format('d/m/Y H:i') }}
                                    <br>
                                    Cập nhật: {{ $product->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
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

        function removeImage(productId, imageId) {
            if (confirm('Bạn có chắc chắn muốn xóa hình ảnh này?')) {
                fetch(`/products/${productId}/images/${imageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById(`image-${imageId}`).remove();
                        } else {
                            alert('Có lỗi xảy ra khi xóa hình ảnh');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi xóa hình ảnh');
                    });
            }
        }
    </script>
@endsection
