@extends('layouts.app')

@section('title', 'Cập nhật hồ sơ')
@section('page-title', 'Cập nhật hồ sơ')

@push('styles')
    <style>
        .profile-card{
            background:#fff;border:1px solid #dee2e6;border-radius:8px;
            box-shadow:0 2px 4px rgba(0,0,0,.05)
        }
        .avatar-wrapper{
            position:relative;width:120px;height:120px;margin:auto;cursor:pointer
        }
        .avatar-wrapper img{
            width:100%;height:100%;object-fit:cover;border-radius:50%;border:2px solid #dee2e6
        }
        .avatar-wrapper input{
            position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer
        }
        .avatar-wrapper:hover::after{
            content:"📷";position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
            background:rgba(0,0,0,.7);color:#fff;padding:8px;border-radius:50%;font-size:16px
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-6">

                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card profile-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Chỉnh sửa thông tin</h5>
                    </div>

                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="card-body">

                            {{-- 1. Avatar --}}
                            <div class="mb-4 text-center">
                                <div class="avatar-wrapper">
                                    <img src="{{ Auth::user()->avatar ? Storage::url(Auth::user()->avatar) : asset('images/avatar-default.png') }}"
                                         alt="Avatar" id="avatar-preview">
                                    <input type="file" name="avatar" accept="image/*" id="avatar-input" title="Đổi ảnh đại diện">
                                </div>
                                <small class="text-muted d-block mt-2">Nhấp vào ảnh để thay đổi</small>
                                @error('avatar')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>

                            {{-- 2. Tên người dùng --}}
                            <div class="mb-3">
                                <label class="form-label">Tên hiển thị</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       name="name" value="{{ old('name', Auth::user()->name) }}" required>
                                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>

                            {{-- 3. Email (readonly) --}}
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="{{ Auth::user()->email }}" readonly>
                                <small class="text-muted">Email không thể thay đổi</small>
                            </div>

                            {{-- 4. Thay đổi mật khẩu --}}
                            <hr>
                            <h6 class="mb-3"><i class="fas fa-lock me-2"></i>Đổi mật khẩu</h6>
                            <p class="text-muted small">Để trống nếu không muốn thay đổi mật khẩu</p>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Mật khẩu hiện tại</label>
                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                           name="current_password">
                                    @error('current_password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mật khẩu mới</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           name="password">
                                    @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Xác nhận mật khẩu</label>
                                    <input type="password" class="form-control"
                                           name="password_confirmation">
                                </div>
                            </div>
                        </div>

                        <div class="card-footer text-end">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary me-2">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Preview avatar trước khi upload
        document.getElementById('avatar-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
@endsection
