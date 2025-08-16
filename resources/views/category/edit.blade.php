@extends('layouts.app')

@section('title', 'Chỉnh sửa danh mục - ' . $category->name)
@section('page-title', 'Chỉnh sửa danh mục')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-edit"></i> Chỉnh sửa: {{ $category->name }}</h5>
                    </div>
                    <form action="{{ route('categories.update', $category) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="card-body row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                                <input name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $category->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Slug <span class="text-danger">*</span></label>
                                <input name="slug" class="form-control @error('slug') is-invalid @enderror"
                                       value="{{ old('slug', $category->slug) }}" required>
                                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Danh mục cha</label>
                                <select name="parent_id" class="form-select">
                                    <option value="">-- Không chọn --</option>
                                    @foreach($categoriesParent as $parent)
                                        <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" class="form-select">
                                    <option value="1" {{ old('status', $category->status) == 1 ? 'selected' : '' }}>Hoạt động</option>
                                    <option value="0" {{ old('status', $category->status) == 0 ? 'selected' : '' }}>Ẩn</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            <a href="{{ route('categories.show', $category->id) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
