@extends('layouts.app')

@section('title', 'Thêm khách hàng mới')
@section('page-title', 'Thêm khách hàng')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-plus me-2"></i>Thông tin khách hàng mới
                        </h5>
                    </div>

                    <form method="POST" action="{{ route('customers.store') }}">
                        @csrf
                        <div class="card-body">
                            <div class="row g-3">
                                {{-- Basic Info --}}
                                <div class="col-md-6">
                                    <label class="form-label" for="name">
                                        <i class="fas fa-user me-1"></i>Tên khách hàng *
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="phone">
                                        <i class="fas fa-phone me-1"></i>Số điện thoại *
                                    </label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                           id="phone" name="phone" value="{{ old('phone') }}" required>
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="email">
                                        <i class="fas fa-envelope me-1"></i>Email
                                    </label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email') }}">
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="birthday">
                                        <i class="fas fa-birthday-cake me-1"></i>Ngày sinh
                                    </label>
                                    <input type="date" class="form-control @error('birthday') is-invalid @enderror"
                                           id="birthday" name="birthday" value="{{ old('birthday') }}">
                                    @error('birthday')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="gender">
                                        <i class="fas fa-venus-mars me-1"></i>Giới tính
                                    </label>
                                    <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                        <option value="">Chọn giới tính</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Nam</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Nữ</option>
                                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Khác</option>
                                    </select>
                                    @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="address">
                                        <i class="fas fa-map-marker-alt me-1"></i>Địa chỉ
                                    </label>
                                    <textarea class="form-control @error('address') is-invalid @enderror"
                                              id="address" name="address" rows="3">{{ old('address') }}</textarea>
                                    @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="notes">
                                        <i class="fas fa-sticky-note me-1"></i>Ghi chú
                                    </label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror"
                                              id="notes" name="notes" rows="3"
                                              placeholder="Ghi chú về khách hàng...">{{ old('notes') }}</textarea>
                                    @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Hủy bỏ
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Lưu khách hàng
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
