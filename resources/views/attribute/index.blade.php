@extends('layouts.app')
@section('title', 'Quản lý thuộc tính')
@section('page-title', 'Thuộc tính sản phẩm')

@push('styles')
    <style>.badge-status{border-radius:4px;padding:0.25rem 0.7rem;font-size:0.95em;}
        .badge-active{background:#e6f9f1;color:#0a7d59;}
        .badge-inactive{background:#fdf2e3;color:#e26b00;}
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card"><div class="card-body text-center py-3"><div class="display-6 fw-bold text-primary">{{ $stats['total'] }}</div><div class="text-muted">Tổng thuộc tính</div></div></div>
            </div>
            <div class="col-md-4">
                <div class="card"><div class="card-body text-center py-3"><div class="display-6 fw-bold text-success">{{ $stats['active'] }}</div><div class="text-muted">Hoạt động</div></div></div>
            </div>
            <div class="col-md-4">
                <div class="card"><div class="card-body text-center py-3"><div class="display-6 fw-bold text-warning">{{ $stats['inactive'] }}</div><div class="text-muted">Ẩn</div></div></div>
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Thuộc tính</h5>
                <a href="{{ route('attributes.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Thêm thuộc tính</a>
            </div>
            <div class="card-body">
                <form class="row g-3 mb-3"><div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                               placeholder="Tìm thuộc tính..." value="{{ request('search') }}">
                    </div></form>
                @if($attributes->count())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>Tên</th>
                                <th>Slug</th>
                                <th>Trạng thái</th>
                                <th>Giá trị</th>
                                <th>Ngày tạo</th>
                                <th width="120">Thao tác</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($attributes as $attr)
                                <tr>
                                    <td><strong>{{ $attr->name }}</strong></td>
                                    <td><code>{{ $attr->slug }}</code></td>
                                    <td>
                            <span class="badge badge-status {{ $attr->status ? 'badge-active' : 'badge-inactive' }}">
                                {{ $attr->status ? 'Hoạt động' : 'Ẩn' }}
                            </span>
                                    </td>
                                    <td>
                                        Có {{ $attr->values()->count() }} giá trị
                                    </td>
                                    <td>{{ $attr->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('attributes.show', $attr) }}" class="btn btn-outline-info btn-sm"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('attributes.edit', $attr) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit"></i></a>
                                            <form method="POST" action="{{ route('attributes.destroy', $attr) }}" style="display:inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                        onclick="return confirm('Xóa thuộc tính này?')"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $attributes->appends(request()->query())->links() }}</div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-tools fa-3x mb-2"></i>
                        <div>Chưa có thuộc tính nào</div>
                        <a href="{{ route('attributes.create') }}" class="btn btn-primary mt-2"><i class="fas fa-plus me-2"></i>Thêm thuộc tính đầu tiên</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
