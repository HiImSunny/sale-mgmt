@extends('layouts.app')
@section('title', 'Chỉnh sửa giá trị - ' . $attribute->name)
@section('page-title', 'Chỉnh sửa giá trị cho: ' . $attribute->name)

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-edit"></i> Chỉnh sửa giá trị
                            <span class="small ms-2">{{ $attribute->name }}</span>
                        </h5>
                    </div>
                    <form action="{{ route('attributes.values.update', [$attribute, $value]) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="card-body row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Giá trị <span class="text-danger">*</span></label>
                                <input name="value" class="form-control @error('value') is-invalid @enderror"
                                       value="{{ old('value', $value->value) }}" required>
                                @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Thứ tự hiển thị</label>
                                <input name="sort_order" type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                       value="{{ old('sort_order', $value->sort_order) }}">
                                @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="card-footer d-flex gap-2 justify-content-end">
                            <a href="{{ route('attributes.show', $attribute) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
