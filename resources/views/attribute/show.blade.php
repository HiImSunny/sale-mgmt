@extends('layouts.app')
@section('title', 'Chi tiết thuộc tính - ' . $attribute->name)
@section('page-title', 'Chi tiết thuộc tính')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-1">{{ $attribute->name }}</h4>
                        <div class="text-muted">Slug: <code>{{ $attribute->slug }}</code></div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Trạng thái:</strong>
                            <span class="badge badge-status {{ $attribute->status ? 'badge-active' : 'badge-inactive' }}">
                            {{ $attribute->status ? 'Hoạt động' : 'Ẩn' }}
                        </span>
                        </div>
                        <div class="mb-3">
                            <strong>Ngày tạo:</strong>
                            {{ $attribute->created_at->format('d/m/Y H:i') }}
                            <small class="text-muted">({{ $attribute->created_at->diffForHumans() }})</small>
                        </div>
                    </div>
                    <div class="card-footer d-flex gap-2 justify-content-end">
                        <a href="{{ route('attributes.edit', $attribute) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Chỉnh sửa
                        </a>
                        <a href="{{ route('attributes.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <!-- Danh sách giá trị thuộc tính ở bên dưới -->
                @if($attribute->values->count())
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">Danh sách giá trị</h6>
                            <a href="{{ route('attributes.values.create', $attribute) }}" class="btn btn-primary btn-sm float-end">
                                <i class="fas fa-plus"></i> Thêm giá trị
                            </a>
                        </div>
                        <div class="card-body pb-0">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Giá trị</th>
                                        <th>Thứ tự</th>
                                        <th>Thao tác</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($attribute->values as $val)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $val->value }}</td>
                                            <td>{{ $val->sort_order }}</td>
                                            <td>
                                                <a href="{{ route('attributes.values.edit', [$attribute, $val]) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit"></i></a>
                                                <form method="POST" action="{{ route('attributes.values.destroy', [$attribute, $val]) }}" style="display:inline;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Xóa giá trị này?')"><i class="fas fa-trash"></i></button>
                                                </form>
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
        </div>
    </div>
@endsection
