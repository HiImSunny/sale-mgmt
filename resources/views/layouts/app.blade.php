{{-- resources/views/layouts/app.blade.php --}}
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ \App\Services\SettingsService::get('general.site_name') }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- CSS Libraries --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Custom CSS --}}
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @stack('styles')

    <style>
        :root {
            --header-height: 60px;
            --navbar-height: 50px;
            --primary-color: #8B4513;
            --accent-color: #D2B48C;
            --light-color: #F5F5F0;
            --dark-color: #2d3748;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-color);
            margin: 0;
            padding: 0;
            padding-top: calc(var(--header-height) + var(--navbar-height));
        }

        /* Top Header */
        .top-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: linear-gradient(135deg, var(--primary-color) 0%, #6B3410 100%);
            color: white;
            z-index: 1001;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .brand-section {
            display: flex;
            align-items: center;
            margin-right: 2rem;
        }

        .brand-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .brand-logo i {
            margin-right: 0.5rem;
            color: var(--accent-color);
        }

        .header-center {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .page-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
            color: white;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-time {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.9);
            margin-right: 1rem;
        }

        .user-menu {
            position: relative;
        }

        .user-menu-btn {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            color: white;
            text-decoration: none;
            transition: all 0.2s;
            backdrop-filter: blur(10px);
        }

        .user-menu-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .user-avatar {
            width: 28px;
            height: 28px;
            background: var(--accent-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.8rem;
            margin-right: 0.5rem;
        }

        /* Main Navigation */
        .main-navigation {
            position: fixed;
            top: var(--header-height);
            left: 0;
            right: 0;
            height: var(--navbar-height);
            background: white;
            border-bottom: 1px solid #e2e8f0;
            z-index: 1000;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .nav-container {
            height: 100%;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .nav-container::-webkit-scrollbar {
            display: none;
        }

        .nav-group {
            display: flex;
            align-items: center;
            margin-right: 2rem;
            min-width: max-content;
        }

        .nav-group:last-child {
            margin-right: 0;
        }

        .nav-group-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--secondary-color);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-right: 1rem;
            min-width: max-content;
        }

        .nav-items {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            color: var(--dark-color);
            text-decoration: none;
            border-radius: 0.375rem;
            transition: all 0.2s;
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
            position: relative;
        }

        .nav-link:hover {
            background-color: var(--light-color);
            color: var(--primary-color);
        }

        .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        .nav-link i {
            width: 16px;
            margin-right: 0.5rem;
            font-size: 0.9rem;
        }

        .nav-badge {
            position: absolute;
            top: -0.25rem;
            right: -0.25rem;
            background-color: var(--danger-color);
            color: white;
            padding: 0.125rem 0.375rem;
            border-radius: 9999px;
            font-size: 0.625rem;
            font-weight: 600;
            min-width: 1.25rem;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            padding: 1.5rem;
            min-height: calc(100vh - var(--header-height) - var(--navbar-height));
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                padding-top: calc(var(--header-height) + 60px);
            }

            .main-navigation {
                height: 60px;
            }

            .nav-container {
                padding: 0.5rem 1rem;
                gap: 1rem;
            }

            .nav-group {
                margin-right: 1.5rem;
            }

            .nav-group-title {
                display: none;
            }

            .nav-link {
                flex-direction: column;
                padding: 0.375rem 0.75rem;
                font-size: 0.75rem;
                text-align: center;
            }

            .nav-link i {
                margin-right: 0;
                margin-bottom: 0.25rem;
                font-size: 1rem;
            }

            .header-time {
                display: none;
            }

            .user-menu-btn .user-info {
                display: none;
            }

            .page-title {
                font-size: 1rem;
            }
        }

        /* Dropdown Menu */
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .dropdown-item {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .dropdown-item:hover {
            background-color: var(--light-color);
            color: var(--primary-color);
        }

        .dropdown-item.text-danger:hover {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }

        /* Utility Classes */
        .badge-status {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .badge-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .badge-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .badge-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .badge-info {
            background-color: rgba(37, 99, 235, 0.1);
            color: #2563eb;
        }
    </style>
</head>

<body>
<div id="app">
    {{-- Top Header --}}
    <header class="top-header">
        <div class="brand-section">
            <a href="{{ route('dashboard') }}" class="brand-logo">
                {{ \App\Services\SettingsService::get('general.site_name') }}
            </a>
        </div>

        <div class="header-center">
            <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
        </div>

        <div class="header-right">
            <div class="header-time" id="currentTime">
                {{ now()->format('H:i:s - d/m/Y') }}
            </div>

            {{-- User Menu --}}
            <div class="user-menu dropdown">
                <a href="#" class="user-menu-btn" data-bs-toggle="dropdown">
                    <div class="user-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="user-info d-none d-md-block">
                        <div style="font-size: 0.875rem; font-weight: 600;">{{ Auth::user()->name }}</div>
                        <div style="font-size: 0.75rem; opacity: 0.8;">{{ ucfirst(Auth::user()->role) }}</div>
                    </div>
                    <i class="fas fa-chevron-down ms-2 d-none d-md-inline"></i>
                </a>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-header">
                        <div class="fw-bold">{{ Auth::user()->name }}</div>
                        <small class="text-muted">{{ Auth::user()->email }}</small>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user me-2"></i>Hồ sơ cá nhân
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    {{-- Main Navigation --}}
    <nav class="main-navigation">
        <div class="nav-container">
            {{-- Core POS Functions - Always visible --}}
            <div class="nav-group primary">
                <div class="nav-items">
                    <a href="{{ route('pos') }}" class="nav-link pos-btn {{ request()->routeIs('pos*') ? 'active' : '' }}">
                        <i class="fas fa-cash-register"></i>
                        <span>Bán hàng</span>
                    </a>
                    <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('order*') ? 'active' : '' }}">
                        <i class="fas fa-receipt"></i>
                        <span>Đơn hàng</span>
                        @if(isset($pendingOrdersCount) && $pendingOrdersCount > 0)
                            <span class="nav-badge">{{ $pendingOrdersCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customer*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Khách hàng</span>
                    </a>
                </div>
            </div>

            {{-- Product Management --}}
            <div class="nav-group">
                <div class="nav-group-title">Sản phẩm</div>
                <div class="nav-items">
                    <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                        <i class="fas fa-box"></i>
                        <span>Kho hàng</span>
                    </a>
                    @if (Auth::user()->role === 'admin')
                        <a href="#categories.index" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                            <i class="fas fa-tags"></i>
                            <span>Danh mục</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Admin Only Sections --}}
            @if(auth()->user()->role === 'admin')
                <div class="nav-group">
                    <div class="nav-group-title">Phân tích</div>
                    <div class="nav-items">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-chart-pie"></i>
                            <span>Thống kê</span>
                        </a>
                        <a href="#reports.sales" class="nav-link {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
                            <i class="fas fa-chart-line"></i>
                            <span>Báo cáo</span>
                        </a>
                    </div>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title">Hệ thống</div>
                    <div class="nav-items">
                        <a href="#users.index" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="fas fa-user-cog"></i>
                            <span>Nhân viên</span>
                        </a>
                        <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs(['settings*', 'backup*']) ? 'active' : '' }}">
                            <i class="fas fa-cog"></i>
                            <span>Cài đặt</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </nav>


    {{-- Main Content --}}
    <main class="main-content">
        @yield('content')
    </main>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Real-time clock
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('vi-VN', {
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        }) + ' - ' + now.toLocaleDateString('vi-VN');

        const timeElement = document.getElementById('currentTime');
        if (timeElement) {
            timeElement.textContent = timeString;
        }
    }

    // Update time every second
    setInterval(updateTime, 1000);
    updateTime(); // Initial call

    // Modern Alert System
    window.PosAlert = {
        success: (title, text = '') => {
            Swal.fire({
                title: title,
                text: text,
                icon: 'success',
                confirmButtonText: 'Tuyệt vời!',
                confirmButtonColor: '#10b981'
            });
        },
        error: (title, text = '') => {
            Swal.fire({
                title: title,
                text: text,
                icon: 'error',
                confirmButtonText: 'Đã hiểu',
                confirmButtonColor: '#ef4444'
            });
        },
        confirm: (title, text, callback) => {
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Xác nhận',
                cancelButtonText: 'Hủy bỏ',
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#64748b'
            }).then((result) => {
                if (result.isConfirmed) {
                    callback();
                }
            });
        }
    };

    // Global CSRF token
    window.Laravel = {
        csrfToken: '{{ csrf_token() }}'
    };

    // Handle session alerts
    @if(session('swal_success'))
    PosAlert.success('{{ session('swal_success') }}');
    @endif

    @if(session('swal_error'))
    PosAlert.error('{{ session('swal_error') }}');
    @endif
</script>

@stack('scripts')
</body>
</html>
