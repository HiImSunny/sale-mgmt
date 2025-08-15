@extends('layouts.app')

@section('title', 'Quản lý sản phẩm')
@section('page-title', 'Sản phẩm')

@push('styles')
    <style>
        .product-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .price-display {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
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
            margin-left: 5px;
        }

        .stock-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .stock-in {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .stock-low {
            background-color: #fff3cd;
            color: #664d03;
        }

        .stock-out {
            background-color: #f8d7da;
            color: #721c24;
        }

        .variant-count {
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        <div class="row g-3 mb-4">
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-primary">{{ $stats['total'] }}</div>
                        <div class="text-muted">Tổng sản phẩm</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-success">{{ $stats['active'] }}</div>
                        <div class="text-muted">Đang hoạt động</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-secondary">{{ $stats['inactive'] }}</div>
                        <div class="text-muted">Không hoạt động</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-info">{{ $stats['in_stock'] }}</div>
                        <div class="text-muted">Còn hàng</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-danger">{{ $stats['out_of_stock'] }}</div>
                        <div class="text-muted">Hết hàng</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-warning">{{ $stats['with_variants'] }}</div>
                        <div class="text-muted">Số lượng biến thể</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-box me-2"></i>Danh sách sản phẩm
                        </h5>

                        <div class="d-flex gap-2">
                            <a href="{{ route('products.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Thêm sản phẩm
                            </a>
                            <button type="button" class="btn btn-success" onclick="exportProducts()">
                                <i class="fas fa-file-excel me-2"></i>Xuất Excel
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()"
                                    style="display: none; "
                                    id="bulk-delete-btn">
                                <i class="fas fa-trash"></i> Xóa đã chọn
                            </button>
                        </div>

                    </div>



                    <div class="card-body">
                        <form method="GET" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="search"
                                       placeholder="Tìm kiếm sản phẩm..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="category">
                                    <option value="">Tất cả danh mục</option>
                                    @foreach($categories as $category)
                                        <option
                                            value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="status">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Hoạt động</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Không hoạt động</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="stock">
                                    <option value="">Tất cả tồn kho</option>
                                    <option value="in_stock" {{ request('stock') === 'in_stock' ? 'selected' : '' }}>Còn hàng
                                    </option>
                                    <option value="out_of_stock" {{ request('stock') === 'out_of_stock' ? 'selected' : '' }}>Hết
                                        hàng
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-50">
                                        <i class="fas fa-search"></i> Tìm kiếm
                                    </button>
                                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary w-50">
                                        <i class="fas fa-undo"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                        @if($products->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" id="select-all" class="form-check-input">
                                        </th>
                                        <th>Sản phẩm</th>
                                        <th>SKU</th>
                                        <th>Danh mục</th>
                                        <th>Giá</th>
                                        <th>Tồn kho</th>
                                        <th>Biến thể</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th width="120">Thao tác</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($products as $product)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input product-checkbox"
                                                       value="{{ $product->id }}">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($product->images->count() > 0)
                                                        <img
                                                            src="{{ Storage::url($product->images->first()->image_url) }}"
                                                            alt="{{ $product->name }}" class="product-thumbnail me-3">
                                                    @else
                                                        <div
                                                            class="product-thumbnail me-3 bg-light d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-1">{{ $product->name }}</h6>
                                                        @if($product->description)
                                                            <small
                                                                class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <code>{{ $product->sku }}</code>
                                            </td>
                                            <td>
                                                @if($product->categories && $product->categories->count() > 0)
                                                    <span
                                                        class="badge bg-light text-dark">{{ $product->categories->first()->name }}</span>
                                                @else
                                                    <span class="text-muted">Chưa phân loại</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="price-display">
                                                    @if($product->sale_price && $product->sale_price < $product->price)
                                                        <span
                                                            class="sale-price">{{ number_format($product->sale_price) }}đ</span>
                                                        <span
                                                            class="original-price">{{ number_format($product->price) }}đ</span>
                                                        <span class="discount-badge">
                                                    -{{ round((($product->price - $product->sale_price) / $product->price) * 100) }}%
                                                </span>
                                                    @else
                                                        <span
                                                            class="fw-bold">{{ number_format($product->price) }}đ</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($product->has_variants)
                                                    <div class="text-center">
                                                <span
                                                    class="stock-badge {{ $product->total_stock > 0 ? 'stock-in' : 'stock-out' }}">
                                                    {{ $product->total_stock }}
                                                </span>
                                                        <div class="variant-count">
                                                            Từ {{ $product->variants->count() }} biến thể
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="text-center">
                                            <span
                                                class="stock-badge {{ $product->stock_quantity > 0 ? 'stock-in' : 'stock-out' }}">
                                                {{ $product->stock_quantity }}
                                            </span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($product->variants->count() > 0)
                                                    <div class="text-center">
                                                        <span
                                                            class="badge bg-primary">{{ $product->variants->count() }}</span>
                                                        <div
                                                            class="small text-muted">{{ $product->variants->sum('stock_quantity') }}
                                                            tổng
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="text-center">
                                                        <span class="text-muted">—</span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($product->status === 1)
                                                    <span class="badge bg-success">Hoạt động</span>
                                                @else
                                                    <span class="badge bg-secondary">Không hoạt động</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ $product->created_at->format('d/m/Y') }}</div>
                                                <small
                                                    class="text-muted">{{ $product->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('products.show', $product) }}"
                                                       class="btn btn-outline-info btn-sm" title="Xem">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if(Auth::user()->role === 'admin')
                                                        <a href="{{ route('products.edit', $product) }}"
                                                           class="btn btn-outline-warning btn-sm" title="Chỉnh sửa">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                                            onclick="deleteProduct({{ $product->id }})" title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="card-footer">
                                {{ $products->appends(request()->query())->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Chưa có sản phẩm nào</h5>
                                <p class="text-muted mb-4">Thêm sản phẩm đầu tiên để bắt đầu</p>
                                <a href="{{ route('products.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Thêm sản phẩm đầu tiên
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Select all functionality
        document.getElementById('select-all').addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('.product-checkbox');
            const bulkDeleteBtn = document.getElementById('bulk-delete-btn');

            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });

            bulkDeleteBtn.style.display = this.checked && checkboxes.length > 0 ? 'inline-block' : 'none';
        });

        // Individual checkbox change
        document.querySelectorAll('.product-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
                const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
                const selectAllCheckbox = document.getElementById('select-all');

                bulkDeleteBtn.style.display = checkedBoxes.length > 0 ? 'inline-block' : 'none';
                selectAllCheckbox.checked = checkedBoxes.length === document.querySelectorAll('.product-checkbox').length;
            });
        });

        // Export products
        function exportProducts() {
            const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');

            if (checkedBoxes.length === 0) {
                // Export all with current filters
                const params = new URLSearchParams(window.location.search);
                window.location.href = '{{ route("products.export") }}?' + params.toString();
            } else {
                // Export selected products
                const productIds = Array.from(checkedBoxes).map(cb => cb.value);
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("products.export") }}';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                productIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'product_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }
        }

        // Delete product
        function deleteProduct(productId) {
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/products/${productId}`;

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

        // Bulk delete
        function bulkDelete() {
            const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');

            if (checkedBoxes.length === 0) {
                alert('Vui lòng chọn ít nhất một sản phẩm để xóa');
                return;
            }

            if (confirm(`Bạn có chắc chắn muốn xóa ${checkedBoxes.length} sản phẩm đã chọn?`)) {
                const productIds = Array.from(checkedBoxes).map(cb => cb.value);

                fetch('{{ route("products.bulk-delete") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        product_ids: productIds
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Có lỗi xảy ra khi xóa sản phẩm');
                        }
                    });
            }
        }
    </script>
@endsection
