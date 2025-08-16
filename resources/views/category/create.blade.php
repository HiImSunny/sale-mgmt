@extends('layouts.app')

@section('title', 'Thêm danh mục mới')
@section('page-title', 'Thêm danh mục')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-plus"></i> Thêm danh mục mới</h5>
                    </div>
                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf
                        <div class="card-body row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                                <input name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Slug <span class="text-danger">*</span></label>
                                <input name="slug" class="form-control @error('slug') is-invalid @enderror"
                                       value="{{ old('slug') }}" required>
                                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Danh mục cha</label>
                                <select name="parent_id" class="form-select">
                                    <option value="">-- Không chọn --</option>
                                    @foreach($categoriesParent as $parent)
                                        <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" class="form-select">
                                    <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Hoạt động</option>
                                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Ẩn</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="{{ route('categories.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Quay lại</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Tạo danh mục</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
