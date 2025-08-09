<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        :root {
            --wimp-primary: #f4f1e8;
            --wimp-secondary: #d4a574;
            --wimp-accent: #8b4513;
            --wimp-text: #2c1810;
            --wimp-light: #faf8f3;
        }

        [data-theme="dark"] {
            --wimp-primary: #2c2416;
            --wimp-secondary: #4a3c28;
            --wimp-accent: #d4a574;
            --wimp-text: #f4f1e8;
            --wimp-light: #1a1610;
        }

        body {
            background-color: var(--wimp-light);
            color: var(--wimp-text);
            font-family: 'Figtree', sans-serif;
        }

        .navbar {
            background-color: var(--wimp-primary) !important;
            border-bottom: 1px solid var(--wimp-secondary);
        }

        .btn-primary {
            background-color: var(--wimp-accent);
            border-color: var(--wimp-accent);
        }

        .btn-primary:hover {
            background-color: var(--wimp-secondary);
            border-color: var(--wimp-secondary);
        }

        .card {
            background-color: var(--wimp-primary);
            border: 1px solid var(--wimp-secondary);
        }

        /* Camera viewport */
        #interactive.viewport {
            width: 100%;
            height: 300px;
            border: 2px dashed var(--wimp-secondary);
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }

        #interactive.viewport canvas,
        #interactive.viewport video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .table-responsive {
            background-color: var(--wimp-primary);
            border-radius: 8px;
            border: 1px solid var(--wimp-secondary);
        }

        /* Search Results Styling */
        #search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1050;
            background: var(--wimp-primary);
            border: 1px solid var(--wimp-secondary);
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .search-result-item {
            padding: 0.75rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: background-color 0.15s ease-in-out;
        }

        .search-result-item:hover {
            background-color: var(--wimp-secondary);
            color: var(--wimp-text);
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        /* Loading spinner positioning */
        .position-relative .spinner-border {
            width: 1rem;
            height: 1rem;
        }

        .scan-guide-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 10;
            pointer-events: none;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .scan-frame {
            width: 60%;
            height: 40%;
            position: relative;
            border: 2px dashed rgba(255, 255, 255, 0.8);
            border-radius: 8px;
        }

        .scan-corners .corner {
            position: absolute;
            width: 20px;
            height: 20px;
            border: 3px solid #4CAF50;
        }

        .scan-corners .top-left {
            top: -3px;
            left: -3px;
            border-right: none;
            border-bottom: none;
        }

        .scan-corners .top-right {
            top: -3px;
            right: -3px;
            border-left: none;
            border-bottom: none;
        }

        .scan-corners .bottom-left {
            bottom: -3px;
            left: -3px;
            border-right: none;
            border-top: none;
        }

        .scan-corners .bottom-right {
            bottom: -3px;
            right: -3px;
            border-left: none;
            border-top: none;
        }

        .scan-line {
            position: absolute;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #4CAF50, transparent);
            top: 50%;
            left: 0;
            animation: scanAnimation 2s ease-in-out infinite;
        }

        @keyframes scanAnimation {

            0%,
            100% {
                transform: translateY(-50%) scaleX(0.5);
                opacity: 0.5;
            }

            50% {
                transform: translateY(-50%) scaleX(1);
                opacity: 1;
            }
        }

        .scan-instructions {
            margin-top: 20px;
            text-align: center;
            color: white;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
        }

        /* Scan status indicator */
        #scan-status {
            font-weight: 500;
        }

        #scan-status.scanning {
            color: #ff9800;
        }

        #scan-status.success {
            color: #4caf50;
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div id="app">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container">
                <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                    {{ config('app.name', 'Sale Management') }}
                </a>

                <div class="navbar-nav ms-auto d-flex flex-row align-items-center">
                    <!-- Dark Mode Toggle -->
                    <button type="button" class="btn btn-link me-3" id="theme-toggle">
                        <i class="fas fa-moon" id="theme-icon"></i>
                    </button>

                    @auth
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu">
                            @if(Auth::user()->isSeller() || Auth::user()->isAdmin())
                            <li><a class="dropdown-item" href="{{ route('seller.pos') }}">POS</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Hồ sơ</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    @else
                    <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">Đăng nhập</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Đăng ký</a>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/25f90b730d.js" crossorigin="anonymous"></script>

    <!-- Dark Mode Toggle Script -->
    <script>
        // Dark mode toggle
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const html = document.documentElement;

        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);
        updateIcon(savedTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcon(newTheme);
        });

        function updateIcon(theme) {
            if (theme === 'dark') {
                themeIcon.className = 'fas fa-sun';
            } else {
                themeIcon.className = 'fas fa-moon';
            }
        }
    </script>

    @stack('scripts')
</body>

</html>