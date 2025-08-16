@extends('layouts.app')
@section('title', 'Thêm giá trị - ' . $attribute->name)
@section('page-title', 'Thêm giá trị cho: ' . $attribute->name)

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-plus"></i> Thêm giá trị thuộc tính
                            <span class="small ms-2">{{ $attribute->name }}</span>
                        </h5>
                    </div>
                    <form action="{{ route('attributes.values.store', $attribute) }}" method="POST">
                        @csrf
                        <div class="card-body row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Giá trị <span class="text-danger">*</span></label>
                                <input name="value" class="form-control @error('value') is-invalid @enderror"
                                       value="{{ old('value') }}" required>
                                @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Thứ tự hiển thị</label>
                                <input name="sort_order" type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                       value="{{ old('sort_order', 0) }}">
                                @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="{{ route('attributes.values.index', $attribute) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Thêm giá trị
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
