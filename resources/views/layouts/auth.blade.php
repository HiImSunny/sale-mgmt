{{-- resources/views/layouts/auth.blade.php - Compact No-Scroll Layout --}}
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - PacificStore POS</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- CSS Libraries --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('styles')

    <style>
        :root {
            --bg-primary: #f5f3f2;
            --bg-secondary: #eae6e3;
            --bg-white: #ffffff;

            --beige-lightest: #f4f1ee;
            --beige-light: #e8dcc6;
            --beige-medium: #d4c5b8;
            --beige-dark: #c4b5a8;

            --brown-light: #a6a397;
            --brown-medium: #93614b;
            --brown-dark: #382a24;
            --brown-darker: #342e2c;

            --gray-lightest: #f8f6f4;
            --gray-light: #e5e2df;
            --gray-medium: #a09892;
            --gray-dark: #866d69;
            --gray-darker: #5d524e;

            --accent-primary: #ae8269;
            --accent-secondary: #978772;
            --accent-warm: #d2c5c5;

            --text-primary: #342e2c;
            --text-secondary: #5d524e;
            --text-light: #866d69;
            --text-muted: #a09892;

            --border-light: #e5e2df;
            --border-medium: #d4c5b8;
            --border-dark: #c4b5a8;

            --shadow-light: rgba(52, 46, 44, 0.08);
            --shadow-medium: rgba(52, 46, 44, 0.12);
            --shadow-strong: rgba(52, 46, 44, 0.16);

            --success: #6b8e23;
            --success-light: #f0f4e8;
            --error: #cd5c5c;
            --error-light: #faf2f2;
            --warning: #d2691e;
            --warning-light: #fdf5f0;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            overflow: hidden; /* ✅ Prevent scrolling */
        }

        body {
            font-family: 'Inter', 'Figtree', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            color: var(--text-primary);
            font-weight: 400;
            line-height: 1.5; /* ✅ Reduced line height */
            letter-spacing: -0.01em;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Subtle background texture */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: radial-gradient(circle at 25% 25%, var(--beige-light) 0%, transparent 50%),
            radial-gradient(circle at 75% 75%, var(--accent-warm) 0%, transparent 50%);
            opacity: 0.3;
            z-index: -1;
        }

        /* ✅ Fixed height container - no scroll */
        .auth-container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            z-index: 1;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 400px; /* ✅ Reduced max-width */
        }

        /* ✅ Compact Header */
        .auth-header {
            text-align: center;
            margin-bottom: 1.5rem; /* ✅ Reduced margin */
        }

        .brand-logo {
            width: 48px;  /* ✅ Smaller logo */
            height: 48px;
            background: linear-gradient(135deg, var(--accent-primary), var(--brown-medium));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem; /* ✅ Reduced margin */
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            box-shadow: 0 4px 15px var(--shadow-medium);
        }

        .brand-title {
            font-family: 'Figtree', sans-serif;
            font-size: 1.5rem; /* ✅ Smaller title */
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 0.25rem; /* ✅ Reduced margin */
            letter-spacing: -0.025em;
        }

        .brand-subtitle {
            color: var(--text-secondary);
            font-size: 0.875rem; /* ✅ Smaller subtitle */
            font-weight: 400;
            margin: 0;
        }

        /* ✅ Compact Card */
        .auth-card {
            background: var(--bg-white);
            border-radius: 16px;
            padding: 2rem 1.5rem; /* ✅ Reduced padding */
            box-shadow: 0 8px 30px var(--shadow-medium);
            border: 1px solid var(--border-light);
            position: relative;
            backdrop-filter: blur(10px);
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent-primary), var(--brown-medium), var(--accent-primary));
            border-radius: 16px 16px 0 0;
        }

        /* ✅ Compact Page Title */
        .auth-page-title {
            margin-bottom: 1.5rem; /* ✅ Reduced margin */
            text-align: center;
        }

        .auth-page-title h2 {
            font-family: 'Figtree', sans-serif;
            font-size: 1.5rem; /* ✅ Smaller heading */
            font-weight: 600;
            color: var(--text-primary);
            margin: 0 0 0.25rem; /* ✅ Reduced margin */
            letter-spacing: -0.02em;
        }

        .auth-page-title p {
            color: var(--text-secondary);
            font-size: 0.875rem; /* ✅ Smaller text */
            margin: 0;
            font-weight: 400;
        }

        /* ✅ Compact Form Elements */
        .form-group {
            margin-bottom: 1.25rem; /* ✅ Reduced margin */
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem; /* ✅ Reduced margin */
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
            font-family: 'Figtree', sans-serif;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem; /* ✅ Reduced padding */
            font-size: 0.95rem;
            border: 2px solid var(--border-light);
            border-radius: 10px;
            background-color: var(--bg-white);
            color: var(--text-primary);
            transition: all 0.3s ease;
            font-family: inherit;
            font-weight: 400;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 0.15rem var(--accent-warm);
            background-color: var(--bg-white);
            color: var(--text-primary);
        }

        .form-control::placeholder {
            color: var(--text-muted);
            font-weight: 400;
        }

        .form-control.is-invalid {
            border-color: var(--error);
            box-shadow: 0 0 0 0.15rem rgba(205, 92, 92, 0.15);
        }

        .invalid-feedback {
            display: block;
            margin-top: 0.25rem; /* ✅ Reduced margin */
            font-size: 0.8rem;
            color: var(--error);
            font-weight: 500;
        }

        /* ✅ Compact Checkbox */
        .form-check {
            display: flex;
            align-items: center;
            margin: 1rem 0; /* ✅ Reduced margin */
        }

        .form-check-input {
            width: 1.1rem; /* ✅ Smaller checkbox */
            height: 1.1rem;
            margin-right: 0.75rem;
            border: 2px solid var(--border-medium);
            border-radius: 4px;
            background-color: var(--bg-white);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-check-input:checked {
            background-color: var(--accent-primary);
            border-color: var(--accent-primary);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e");
        }

        .form-check-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            cursor: pointer;
            user-select: none;
            font-weight: 400;
        }

        /* ✅ Compact Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem; /* ✅ Reduced padding */
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-family: 'Figtree', sans-serif;
            line-height: 1.4;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: var(--accent-primary);
            color: white;
            width: 100%;
            box-shadow: 0 3px 12px var(--shadow-medium);
        }

        .btn-primary:hover {
            background: var(--brown-medium);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px var(--shadow-strong);
            color: white;
        }

        .btn-outline-secondary {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border-medium);
        }

        .btn-outline-secondary:hover {
            background: var(--beige-light);
            color: var(--text-primary);
            border-color: var(--accent-primary);
        }

        .btn-sm {
            padding: 0.5rem 1rem; /* ✅ Smaller button padding */
            font-size: 0.8rem;
        }

        /* Loading State */
        .btn-loading {
            position: relative;
            color: transparent !important;
            pointer-events: none;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid transparent;
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ✅ Compact Alert */
        .alert {
            padding: 0.75rem 1rem; /* ✅ Reduced padding */
            border-radius: 10px;
            margin-bottom: 1rem; /* ✅ Reduced margin */
            font-size: 0.875rem;
            border: 1px solid transparent;
            font-weight: 500;
        }

        .alert-success {
            background-color: var(--success-light);
            border-color: rgba(107, 142, 35, 0.3);
            color: var(--success);
        }

        .alert-danger {
            background-color: var(--error-light);
            border-color: rgba(205, 92, 92, 0.3);
            color: var(--error);
        }

        /* ✅ Responsive - Maintain no-scroll */
        @media (max-width: 480px) {
            .auth-container {
                padding: 0.5rem;
            }

            .auth-card {
                padding: 1.5rem 1rem;
            }

            .brand-title {
                font-size: 1.25rem;
            }

            .brand-logo {
                width: 40px;
                height: 40px;
                font-size: 1.25rem;
            }

            .auth-page-title h2 {
                font-size: 1.25rem;
            }
        }

        /* ✅ Vertical mobile - ensure no scroll */
        @media (max-height: 600px) {
            .auth-header {
                margin-bottom: 1rem;
            }

            .auth-page-title {
                margin-bottom: 1rem;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .brand-logo {
                width: 36px;
                height: 36px;
                font-size: 1.1rem;
            }
        }

        /* SweetAlert2 custom styling */
        .swal2-popup {
            border-radius: 12px !important;
        }

        .swal2-confirm {
            background: var(--accent-primary) !important;
            border-radius: 8px !important;
        }
    </style>
</head>

<body>
<div id="app">
    <div class="auth-container">
        <div class="auth-wrapper">
            {{-- Header --}}
            <div class="auth-header">
                <div class="brand-logo">
                    <i class="fas fa-store"></i>
                </div>
                <h1 class="brand-title">PacificStore</h1>
                <p class="brand-subtitle">Quản lý bán hàng</p>
            </div>

            {{-- Card --}}
            <div class="auth-card">
                @yield('content')
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Compact Alert System
    window.AuthAlert = {
        success: (title, text = '') => {
            Swal.fire({
                title: title,
                text: text,
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#ae8269',
                timer: 3000
            });
        },
        error: (title, text = '') => {
            Swal.fire({
                title: title,
                text: text,
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#cd5c5c'
            });
        }
    };

    // Global CSRF token
    window.Laravel = {
        csrfToken: '{{ csrf_token() }}'
    };

    // Form handling - compact
    document.addEventListener('DOMContentLoaded', function() {
        const firstInput = document.querySelector('.form-control');
        if (firstInput) {
            firstInput.focus();
        }

        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    submitBtn.classList.add('btn-loading');
                    submitBtn.disabled = true;
                }
            });
        });
    });

    // Handle session alerts
    @if(session('status'))
    AuthAlert.success('{{ session('status') }}');
    @endif

    @if(session('error'))
    AuthAlert.error('Lỗi', '{{ session('error') }}');
    @endif

    @if($errors->any())
    AuthAlert.error('Đăng nhập thất bại', '{{ $errors->first() }}');
    @endif
</script>

@stack('scripts')
</body>
</html>
