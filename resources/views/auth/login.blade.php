{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.auth')

@section('title', 'Đăng nhập hệ thống POS')

@section('content')
    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Page Title - Compact --}}
        <div class="auth-page-title">
            <h2>Đăng nhập POS</h2>
            <p>Dành cho Admin & Nhân viên bán hàng</p>
        </div>

        {{-- Session Status --}}
        @if (session('status'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('status') }}
            </div>
        @endif

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Email Field --}}
        <div class="form-group">
            <label for="email" class="form-label">
                <i class="fas fa-user me-2 text-muted"></i>Tài khoản
            </label>
            <input id="email"
                   type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   autocomplete="email"
                   autofocus
                   placeholder="Nhập email tài khoản">
            @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password Field --}}
        <div class="form-group">
            <label for="password" class="form-label">
                <i class="fas fa-lock me-2 text-muted"></i>Mật khẩu
            </label>
            <div class="position-relative">
                <input id="password"
                       type="password"
                       class="form-control @error('password') is-invalid @enderror"
                       name="password"
                       required
                       autocomplete="current-password"
                       placeholder="Nhập mật khẩu">
                <button type="button"
                        class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3"
                        style="border: none; background: none; color: var(--text-muted); z-index: 10;"
                        onclick="togglePasswordVisibility()">
                    <i class="fas fa-eye" id="password-toggle-icon"></i>
                </button>
            </div>
            @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input"
                       type="checkbox"
                       name="remember"
                       id="remember"
                    {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">
                    Ghi nhớ đăng nhập
                </label>
            </div>
        </div>

        {{-- Submit Button --}}
        <button type="submit" class="btn btn-primary mb-3">
            <i class="fas fa-sign-in-alt me-2"></i>
            Đăng nhập hệ thống
        </button>
    </form>

    {{-- Compact Scripts --}}
    @push('scripts')
        <script>
            // Password visibility toggle
            function togglePasswordVisibility() {
                const passwordInput = document.getElementById('password');
                const toggleIcon = document.getElementById('password-toggle-icon');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                }
            }

            // Demo account fillers (development only)
            @if (app()->environment('local', 'staging'))
            function fillDemoAdmin() {
                document.getElementById('email').value = 'admin@pacificstore.com';
                document.getElementById('password').value = 'admin123';
            }

            function fillDemoSeller() {
                document.getElementById('email').value = 'seller@pacificstore.com';
                document.getElementById('password').value = 'seller123';
            }
            @endif

            // Enhanced keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    const activeElement = document.activeElement;
                    if (activeElement.id === 'email') {
                        document.getElementById('password').focus();
                        e.preventDefault();
                    }
                }
            });

            // Auto-clear errors on input
            document.querySelectorAll('.form-control').forEach(input => {
                input.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                });
            });
        </script>
    @endpush
@endsection
