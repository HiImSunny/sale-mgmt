
@extends('layouts.app')

@section('title', 'Quản lý danh mục')
@section('page-title', 'Danh mục sản phẩm')

@push('styles')
    <style>
        .badge-status { border-radius: 4px; padding: 0.25rem 0.7rem; font-size: 0.95em;}
        .badge-active { background: #e6f9f1; color: #0a7d59;}
        .badge-inactive { background: #fdf2e3; color: #e26b00;}
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        <div class="row g-3 mb-4">
            <div class="col-4">
                <div class="card">
                    <div class="card-body text-center py-3">
                        <div class="display-6 fw-bold text-primary">
                            {{ $stats['total'] }}
                        </div>
                        <div class="text-muted">Tổng danh mục</div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card">
                    <div class="card-body text-center py-3">
                        <div class="display-6 fw-bold text-success">
                            {{ $stats['active'] }}
                        </div>
                        <div class="text-muted">Đang hoạt động</div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card">
                    <div class="card-body text-center py-3">
                        <div class="display-6 fw-bold text-warning">
                            {{ $stats['inactive'] }}
                        </div>
                        <div class="text-muted">Đã ẩn</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Control -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list-alt me-2"></i>Danh sách danh mục
                </h5>
                <a href="{{ route('categories.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Thêm danh mục
                </a>
            </div>
            <div class="card-body">
                <form class="row g-3 mb-3" method="get">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control"
                               placeholder="Tìm kiếm tên/slug..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="1"{{ request('status')==='1' ? ' selected' : '' }}>Hoạt động</option>
                            <option value="0"{{ request('status')==='0' ? ' selected' : '' }}>Ẩn</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-50">
                                <i class="fas fa-search"></i> Tìm kiếm
                            </button>
                            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary w-50">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                @if($categories->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>Tên danh mục</th>
                                <th>Slug</th>
                                <th>Danh mục cha</th>
                                <th>Trạng thái</th>
                                <th>Tạo lúc</th>
                                <th width="120">Thao tác</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($categories as $cat)
                                <tr>
                                    <td>
                                        <strong>{{ $cat->name }}</strong>
                                    </td>
                                    <td><code>{{ $cat->slug }}</code></td>
                                    <td>
                                        {{ optional($cat->parent)->name ?? '-' }}
                                    </td>
                                    <td>
                                <span class="badge badge-status {{ $cat->status ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $cat->status ? 'Hoạt động' : 'Ẩn' }}
                                </span>
                                    </td>
                                    <td>
                                        {{ $cat->created_at->format('d/m/Y') }}
                                        <div><small class="text-muted">{{ $cat->created_at->diffForHumans() }}</small></div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('categories.show', $cat) }}" class="btn btn-outline-info btn-sm"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('categories.edit', $cat) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit"></i></a>
                                            <form method="post" action="{{ route('categories.destroy', $cat) }}" style="">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                        onclick="return confirm('Xóa danh mục này?')"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $categories->appends(request()->query())->links() }}</div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-list-ul fa-3x mb-2"></i>
                        <div>Chưa có danh mục nào</div>
                        <a href="{{ route('categories.create') }}" class="btn btn-primary mt-2"><i class="fas fa-plus me-2"></i>Thêm danh mục đầu tiên</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
