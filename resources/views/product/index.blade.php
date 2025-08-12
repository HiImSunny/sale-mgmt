@extends('layouts.app')

@section('title', 'Quản lý sản phẩm')
@section('page-title', 'Sản phẩm')

@push('styles')
    <style>
        .stats-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px var(--shadow-light);
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px var(--shadow-medium);
        }

        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }

        .product-placeholder {
            width: 50px;
            height: 50px;
            background: var(--gray-light);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-dark);
        }

        .status-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background: var(--success-light);
            color: var(--success);
        }

        .status-inactive {
            background: var(--error-light);
            color: var(--error);
        }

        .stock-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-weight: 500;
        }

        .stock-in {
            background: var(--success-light);
            color: var(--success);
        }

        .stock-out {
            background: var(--error-light);
            color: var(--error);
        }

        .search-box {
            background: var(--bg-white);
            border: 2px solid var(--border-light);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .search-box:focus {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 0.2rem var(--accent-warm);
        }

        .bulk-actions {
            background: var(--accent-warm);
            border: 1px solid var(--accent-primary);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: none;
        }

        .variant-count {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        {{-- Statistics Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-primary">{{ number_format($stats['total']) }}</div>
                        <div class="text-muted">Tổng sản phẩm</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-success">{{ $stats['active'] }}</div>
                        <div class="text-muted">Hoạt động</div>
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
                        <div class="display-6 fw-bold text-warning">{{ $stats['out_of_stock'] }}</div>
                        <div class="text-muted">Hết hàng</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold" style="color: #7b1fa2;">{{ $stats['with_variants'] }}</div>
                        <div class="text-muted">Có biến thể</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-box me-2"></i>Danh sách sản phẩm
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-success" onclick="exportProducts()">
                                <i class="fas fa-file-excel me-2"></i>Xuất Excel
                            </button>
                            <a href="{{ route('products.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Thêm sản phẩm
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Bulk Actions --}}
                        <div class="bulk-actions" id="bulkActions">
                            <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">
                                <span id="selectedCount">0</span> sản phẩm đã chọn
                            </span>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()">
                                        <i class="fas fa-trash me-1"></i>Xóa đã chọn
                                    </button>
                                    <button type="button" class="btn btn-light btn-sm" onclick="clearSelection()">
                                        <i class="fas fa-times me-1"></i>Hủy chọn
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Filters --}}
                        <form method="GET" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <input type="text" name="search"
                                       class="form-control search-box"
                                       placeholder="Tìm kiếm tên, SKU..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="category" class="form-select">
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
                                <select name="status" class="form-select">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hoạt
                                        động
                                    </option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                                        Không hoạt động
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="stock" class="form-select">
                                    <option value="">Tất cả tồn kho</option>
                                    <option value="in_stock" {{ request('stock') === 'in_stock' ? 'selected' : '' }}>Còn
                                        hàng
                                    </option>
                                    <option
                                        value="out_of_stock" {{ request('stock') === 'out_of_stock' ? 'selected' : '' }}>
                                        Hết hàng
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="sort" class="form-select">
                                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>
                                        Ngày tạo
                                    </option>
                                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Tên</option>
                                    <option value="price" {{ request('sort') == 'price' ? 'selected' : '' }}>Giá
                                    </option>
                                    <option value="stock" {{ request('sort') == 'stock' ? 'selected' : '' }}>Tồn kho
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>

                        {{-- Product Table --}}
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>Sản phẩm</th>
                                    <th>SKU</th>
                                    <th>Danh mục</th>
                                    <th>Giá</th>
                                    <th>Tồn kho</th>
                                    <th>Biến thể</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($products as $product)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="product_ids[]" value="{{ $product->id }}"
                                                   class="form-check-input product-checkbox">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($product->images->count() > 0)
                                                    <img src="{{ Storage::url($product->images->first()->image_url) }}"
                                                         alt="{{ $product->name }}"
                                                         class="product-image me-3">
                                                @else
                                                    <div class="product-placeholder me-3">
                                                        <i class="fas fa-image"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-bold">{{ $product->name }}</div>
                                                    @if($product->description)
                                                        <small class="text-muted">
                                                            {{ Str::limit($product->description, 50) }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <code class="bg-light p-1 rounded">{{ $product->sku }}</code>
                                        </td>
                                        <td>
                                            @if($product->category)
                                                <span
                                                    class="badge bg-light text-dark">{{ $product->category->name }}</span>
                                            @else
                                                <span class="text-muted">Chưa phân loại</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold text-success">{{ number_format($product->price) }}đ
                                            </div>
                                        </td>
                                        <td>
                                            @if($product->has_variants)
                                                <div class="text-center">
            <span class="stock-badge {{ $product->total_stock > 0 ? 'stock-in' : 'stock-out' }}">
                {{ $product->total_stock }}
            </span>
                                                    <div class="variant-count">
                                                        Từ {{ $product->variants->count() }} biến thể
                                                    </div>
                                                </div>
                                            @else
                                                <span
                                                    class="stock-badge {{ $product->stock_quantity > 0 ? 'stock-in' : 'stock-out' }}">
            {{ $product->stock_quantity }}
        </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($product->variants->count() > 0)
                                                <div class="text-center">
                                                    <div class="fw-bold">{{ $product->variants->count() }}</div>
                                                    <div class="variant-count">
                                                        {{ $product->variants->sum('stock_quantity') }} tổng
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted text-center d-block">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="status-badge status-{{ $product->status }}">
                                                {{ $product->status === 1 ? 'Hoạt động' : 'Không hoạt động' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>{{ $product->created_at->format('d/m/Y') }}</div>
                                            <small
                                                class="text-muted">{{ $product->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('products.show', $product) }}"
                                                   class="btn btn-outline-info" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('products.edit', $product) }}"
                                                   class="btn btn-outline-primary" title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger"
                                                        onclick="deleteProduct({{ $product->id }})" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5">
                                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">Chưa có sản phẩm nào</h6>
                                            <a href="{{ route('products.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus me-2"></i>Thêm sản phẩm đầu tiên
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @if($products->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $products->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Select All functionality
        document.getElementById('selectAll').addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('.product-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });

        // Individual checkbox functionality
        document.querySelectorAll('.product-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActions);
        });

        function updateBulkActions() {
            const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
            const bulkActions = document.getElementById('bulkActions');
            const selectedCount = document.getElementById('selectedCount');

            if (checkedBoxes.length > 0) {
                bulkActions.style.display = 'block';
                selectedCount.textContent = checkedBoxes.length;
            } else {
                bulkActions.style.display = 'none';
                document.getElementById('selectAll').checked = false;
            }
        }

        function clearSelection() {
            document.querySelectorAll('.product-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('selectAll').checked = false;
            updateBulkActions();
        }

        function bulkDelete() {
            const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Vui lòng chọn ít nhất một sản phẩm để xóa.');
                return;
            }

            if (confirm(`Bạn có chắc chắn muốn xóa ${checkedBoxes.length} sản phẩm đã chọn? Thao tác này không thể hoàn tác.`)) {
                const productIds = Array.from(checkedBoxes).map(cb => cb.value);

                fetch('{{ route("products.bulk-delete") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        product_ids: productIds
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert('Có lỗi xảy ra khi xóa sản phẩm.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi xóa sản phẩm.');
                    });
            }
        }

        function deleteProduct(productId) {
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này? Thao tác này không thể hoàn tác.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/products/${productId}`;
                form.innerHTML = `
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
        `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function exportProducts() {
            // Show loading
            const exportBtn = document.querySelector('[onclick="exportProducts()"]');
            const originalText = exportBtn.innerHTML;
            exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang xuất...';
            exportBtn.disabled = true;

            // Get current filter parameters
            const params = new URLSearchParams(window.location.search);
            const exportUrl = '{{ route("products.export") }}?' + params.toString();

            // Create temporary link and download
            const link = document.createElement('a');
            link.href = exportUrl;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Reset button after delay
            setTimeout(() => {
                exportBtn.innerHTML = originalText;
                exportBtn.disabled = false;
            }, 2000);
        }
    </script>
@endpush
