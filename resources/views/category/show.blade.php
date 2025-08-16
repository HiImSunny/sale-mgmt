@extends('layouts.app')

@section('title', 'Chi tiết danh mục - ' . $category->name)
@section('page-title', 'Chi tiết danh mục')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-1">{{ $category->name }}</h4>
                        <div class="text-muted">Slug: <code>{{ $category->slug }}</code></div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Thuộc danh mục cha:</strong>
                            <span class="text-primary">{{ optional($category->parent)->name ?? '(Không có)' }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Trạng thái:</strong>
                            <span class="badge badge-status {{ $category->status ? 'badge-active' : 'badge-inactive' }}">
                            {{ $category->status ? 'Hoạt động' : 'Ẩn' }}
                        </span>
                        </div>
                        <div class="mb-3">
                            <strong>Ngày tạo:</strong>
                            {{ $category->created_at->format('d/m/Y H:i') }}
                            <small class="text-muted">({{ $category->created_at->diffForHumans() }})</small>
                        </div>
                        <div class="mb-3">
                            <strong>Có {{ $category->children->count() }} danh mục con</strong><br>
                            @if($category->children->count() > 0)
                                @foreach($category->children as $child)
                                    <div>
                                        <i class="fas fa-angle-right text-primary"></i>
                                        <a href="{{ route('categories.show', $child) }}">{{ $child->name }}</a>
                                    </div>
                                @endforeach
                            @else
                                <span class="text-muted">Không có danh mục con</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer d-flex gap-2 justify-content-end">
                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Chỉnh sửa
                        </a>
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
