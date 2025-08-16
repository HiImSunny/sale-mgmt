@extends('layouts.app')

@section('title', 'Chỉnh sửa người dùng - ' . $user->name)
@section('page-title', 'Chỉnh sửa người dùng')

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
            position: relative;
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

        .current-avatar {
            position: relative;
        }

        .avatar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .avatar-preview:hover .avatar-overlay {
            opacity: 1;
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
                            <i class="fas fa-user-edit me-2"></i>Cập nhật thông tin người dùng: {{ $user->email }}
                        </h5>
                        <small class="text-muted">Chỉnh sửa thông tin tài khoản</small>
                    </div>

                    <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="card-body">
                            <!-- Current Avatar -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label class="form-label">Avatar hiện tại</label>
                                    <div class="avatar-upload">
                                        <div class="avatar-preview" onclick="document.getElementById('avatar-input').click()">
                                            @if($user->avatar)
                                                <div class="current-avatar">
                                                    <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}">
                                                    <div class="avatar-overlay">
                                                        <i class="fas fa-camera fa-2x text-white"></i>
                                                    </div>
                                                </div>
                                            @else
                                                <i class="fas fa-camera fa-2x text-muted"></i>
                                            @endif
                                        </div>
                                        <input type="file" id="avatar-input" class="avatar-input" name="avatar"
                                               accept="image/*" onchange="previewAvatar(this)">
                                    </div>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">Kéo thả hoặc click để thay đổi avatar</small>
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
                                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                        @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Mật khẩu mới</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                               id="password" name="password">
                                        <small class="text-muted">Để trống nếu không muốn thay đổi</small>
                                        @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
                                        <input type="password" class="form-control"
                                               id="password_confirmation" name="password_confirmation">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Vai trò <span class="text-danger">*</span></label>
                                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                            <option value="">Chọn vai trò</option>
                                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                                                Quản trị viên
                                            </option>
                                            <option value="seller" {{ old('role', $user->role) == 'seller' ? 'selected' : '' }}>
                                                Nhân viên bán hàng
                                            </option>
                                        </select>
                                        @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Account Info -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">Thông tin tài khoản</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <small class="text-muted">ID: {{ $user->id }}</small>
                                                </div>
                                                <div class="col-md-6">
                                                    <small class="text-muted">Tạo lúc: {{ $user->created_at->format('d/m/Y H:i:s') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer d-flex justify-content-between">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                            <div class="d-flex gap-2">
                                <a href="{{ route('users.show', $user) }}" class="btn btn-outline-info">
                                    <i class="fas fa-eye me-2"></i>Xem chi tiết
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Cập nhật
                                </button>
                            </div>
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
                    preview.innerHTML = `
                    <div class="current-avatar">
                        <img src="${e.target.result}" alt="Avatar Preview">
                        <div class="avatar-overlay">
                            <i class="fas fa-camera fa-2x text-white"></i>
                        </div>
                    </div>
                `;
                }

                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
