
@extends('layouts.app')

@section('title', 'Thêm người dùng mới')
@section('page-title', 'Thêm người dùng')

@push('styles')
    <style>
        .avatar-upload {
            position: relative;
            max-width: 150px;
            margin: 0 auto;
        }

        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px dashed #dee2e6;
            cursor: pointer;
            transition: all 0.3s;
        }

        .avatar-preview:hover {
            border-color: #007bff;
            background: #f0f8ff;
        }

        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .avatar-input {
            display: none;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-plus me-2"></i>Tạo người dùng mới trong hệ thống
                        </h5>
                        <small class="text-muted">Điền thông tin cơ bản cho tài khoản mới</small>
                    </div>

                    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <!-- Avatar Upload -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label class="form-label">Avatar</label>
                                    <div class="avatar-upload">
                                        <div class="avatar-preview" onclick="document.getElementById('avatar-input').click()">
                                            <i class="fas fa-camera fa-2x text-muted"></i>
                                        </div>
                                        <input type="file" id="avatar-input" class="avatar-input" name="avatar"
                                               accept="image/*" onchange="previewAvatar(this)">
                                    </div>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">Kéo thả hoặc click để chọn avatar</small>
                                    </div>
                                    @error('avatar')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <!-- Basic Info -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               id="name" name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               id="email" name="email" value="{{ old('email') }}" required>
                                        @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                               id="password" name="password" required>
                                        @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control"
                                               id="password_confirmation" name="password_confirmation" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Vai trò <span class="text-danger">*</span></label>
                                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                            <option value="">Chọn vai trò</option>
                                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>
                                                <i class="fas fa-crown"></i> Quản trị viên
                                            </option>
                                            <option value="seller" {{ old('role') == 'seller' ? 'selected' : '' }}>
                                                <i class="fas fa-store"></i> Nhân viên bán hàng
                                            </option>
                                        </select>
                                        @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer d-flex justify-content-between">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Tạo người dùng
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const preview = document.querySelector('.avatar-preview');
                    preview.innerHTML = `<img src="${e.target.result}" alt="Avatar Preview">`;
                }

                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
