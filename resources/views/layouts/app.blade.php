<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - PacificStore</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
          rel="stylesheet">

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
            --sidebar-width: 280px;
            --header-height: 70px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-color);
            margin: 0;
            padding: 0;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #334155 100%);
            color: white;
            z-index: 1000;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .sidebar-brand i {
            margin-right: 0.5rem;
            color: var(--accent-color);
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-section-title {
            padding: 0 1.5rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.6);
            letter-spacing: 0.05em;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            position: relative;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(4px);
        }

        .nav-link.active {
            background-color: var(--accent-color);
            color: white;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background-color: white;
        }

        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }

        .nav-badge {
            margin-left: auto;
            background-color: var(--danger-color);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Main Content */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .main-header {
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--dark-color);
            margin-right: 1rem;
            padding: 0.5rem;
            border-radius: 0.375rem;
            transition: background-color 0.2s;
        }

        .sidebar-toggle:hover {
            background-color: var(--light-color);
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark-color);
            margin: 0;
        }

        .header-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-notifications {
            position: relative;
        }

        .notification-btn {
            background: none;
            border: none;
            color: var(--secondary-color);
            font-size: 1.25rem;
            padding: 0.5rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
            position: relative;
        }

        .notification-btn:hover {
            background-color: var(--light-color);
            color: var(--dark-color);
        }

        .notification-badge {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            background-color: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 8px;
            height: 8px;
        }

        .user-dropdown {
            position: relative;
        }

        .user-menu-btn {
            display: flex;
            align-items: center;
            background: none;
            border: none;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: background-color 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .user-menu-btn:hover {
            background-color: var(--light-color);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 0.5rem;
        }

        .user-info h6 {
            margin: 0;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--dark-color);
        }

        .user-info small {
            color: var(--secondary-color);
            font-size: 0.75rem;
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            padding: 2rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: block;
            }

            .main-content {
                padding: 1rem;
            }

            .header-right .user-info {
                display: none;
            }
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        @media (max-width: 768px) {
            .sidebar-overlay.show {
                display: block;
            }
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
    {{-- Sidebar --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}" class="sidebar-brand">
                PacificStore
            </a>
        </div>

        <nav class="sidebar-nav">
            @if(auth()->user()->role === 'admin')
                <div class="nav-section">
                    <div class="nav-section-title">Tổng quan</div>
                    <a href="{{ route('dashboard') }}"
                       class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </div>
            @endif


            {{-- POS --}}
            <div class="nav-section">
                <div class="nav-section-title">Bán hàng</div>
                <a href="{{ route('pos') }}" class="nav-link {{ request()->routeIs('pos*') ? 'active' : '' }}">
                    <i class="fas fa-cash-register"></i>
                    Bán hàng tại quầy
                </a>
                <a href="{{ route('customers.index')  }}" class="nav-link {{ request()->routeIs('customer*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    Khách hàng
                </a>
                <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('order*') ? 'active' : '' }}">
                    <i class="fas fa-receipt"></i>
                    Đơn hàng
                    @if(isset($pendingOrdersCount) && $pendingOrdersCount > 0)
                        <span class="nav-badge">{{ $pendingOrdersCount }}</span>
                    @endif
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Sản phẩm</div>
                <a href="#products.index" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="fas fa-box"></i>
                    Sản phẩm
                </a>
                <a href="#categories.index" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i>
                    Danh mục
                </a>
                <a href="#inventory.index" class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                    <i class="fas fa-warehouse"></i>
                    Tồn kho
                </a>
            </div>

            @if(auth()->user()->role === 'admin')
                <div class="nav-section">
                    <div class="nav-section-title">Báo cáo</div>
                    <a href="#reports.sales" class="nav-link {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        Báo cáo bán hàng
                    </a>
                    <a href="#reports.inventory"
                       class="nav-link {{ request()->routeIs('reports.inventory') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i>
                        Báo cáo tồn kho
                    </a>
                </div>
            @endif

            @if(auth()->user()->role === 'admin')
                <div class="nav-section">
                    <div class="nav-section-title">Quản trị</div>
                    <a href="#users.index" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        Người dùng
                    </a>
                    <a href="#backup.index" class="nav-link {{ request()->routeIs('database.*') ? 'active' : '' }}">
                        <i class="fas fa-database"></i>
                        Sao lưu & khôi phục
                    </a>
                    <a href="#settings.index" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <i class="fas fa-cog"></i>
                        Cài đặt
                    </a>
                </div>
            @endif
        </nav>
    </aside>

    {{-- Sidebar Overlay for Mobile --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- Main Content --}}
    <div class="main-wrapper">
        {{-- Header --}}
        <header class="main-header">
            <div class="header-left">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
            </div>

            <div class="header-right">


                {{-- User Menu --}}
                <div class="user-dropdown dropdown">
                    <a href="#" class="user-menu-btn" data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="user-info">
                            <h6>{{ Auth::user()->name }}</h6>
                            <small>{{ ucfirst(Auth::user()->role) }}</small>
                        </div>
                        <i class="fas fa-chevron-down ms-2"></i>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header">
                            <div class="fw-bold">{{ Auth::user()->name }}</div>
                            <small class="text-muted">{{ Auth::user()->email }}</small>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item" href="#profile.edit">
                                <i class="fas fa-user me-2"></i>Hồ sơ cá nhân
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#settings.index">
                                <i class="fas fa-cog me-2"></i>Cài đặt
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
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

        {{-- Main Content Area --}}
        <main class="main-content">
            @yield('content')
        </main>
    </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Sidebar Toggle
    document.addEventListener('DOMContentLoaded', function () {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        }

        sidebarToggle?.addEventListener('click', toggleSidebar);
        sidebarOverlay?.addEventListener('click', toggleSidebar);

        // Close sidebar when clicking on nav links (mobile)
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                }
            });
        });
    });

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
