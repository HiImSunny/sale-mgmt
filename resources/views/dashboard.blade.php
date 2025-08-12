@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
    <style>
        .overview-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px var(--shadow-light);
            transition: all 0.3s ease;
        }

        .overview-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px var(--shadow-medium);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .stat-icon.bg-primary { background: var(--accent-primary); }
        .stat-icon.bg-success { background: var(--success); }
        .stat-icon.bg-warning { background: var(--warning); }
        .stat-icon.bg-info { background: #3498db; }
        .stat-icon.bg-secondary { background: var(--gray-dark); }
        .stat-icon.bg-dark { background: var(--brown-dark); }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .badge-status {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
        }

        .badge-success { background: var(--success-light); color: var(--success); }
        .badge-warning { background: var(--warning-light); color: var(--warning); }
        .badge-danger { background: var(--error-light); color: var(--error); }
        .badge-info { background: rgba(52, 152, 219, 0.1); color: #2980b9; }

        .tier-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .tier-bronze { background: var(--gray-light); color: var(--gray-dark); }
        .tier-silver { background: #e8f4fd; color: #1565c0; }
        .tier-gold { background: #fff8e1; color: #ef6c00; }
        .tier-platinum { background: #f3e5f5; color: #7b1fa2; }

        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid var(--border-light);
            transition: background-color 0.2s ease;
        }

        .activity-item:hover {
            background-color: var(--bg-primary);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .quick-action-btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .quick-action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card overview-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-primary flex-shrink-0">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <h3 class="mb-1 fs-4">{{ number_format($analytics['todaySales']) }}đ</h3>
                                <p class="text-muted mb-1 small">Doanh thu hôm nay</p>
                                @if(isset($analytics['salesGrowth']))
                                    <small class="text-{{ $analytics['salesGrowth'] >= 0 ? 'success' : 'danger' }} fw-bold">
                                        <i class="fas fa-arrow-{{ $analytics['salesGrowth'] >= 0 ? 'up' : 'down' }}"></i>
                                        {{ abs($analytics['salesGrowth']) }}%
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card overview-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-success flex-shrink-0">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <h3 class="mb-1 fs-4">{{ $analytics['todayOrders'] }}</h3>
                                <p class="text-muted mb-1 small">Đơn hàng hôm nay</p>
                                <small class="text-muted">
                                    <i class="fas fa-check-circle me-1"></i>
                                    {{ $analytics['completedOrders'] }} hoàn thành
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 3: Sản phẩm thiếu hàng --}}
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card overview-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-warning flex-shrink-0">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <h3 class="mb-1 fs-4">{{ $analytics['lowStockCount'] }}</h3>
                                <p class="text-muted mb-1 small">Sản phẩm thiếu hàng</p>
                                @if($analytics['lowStockCount'] > 0)
                                    <small class="text-danger fw-bold">
                                        <i class="fas fa-warning me-1"></i>
                                        Cần bổ sung
                                    </small>
                                @else
                                    <small class="text-success fw-bold">
                                        <i class="fas fa-check me-1"></i>
                                        Đầy đủ
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 4: Khách hàng mới --}}
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card overview-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-info flex-shrink-0">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <h3 class="mb-1 fs-4">{{ $analytics['newCustomersToday'] }}</h3>
                                <p class="text-muted mb-1 small">Khách hàng mới</p>
                                <small class="text-info">
                                    <i class="fas fa-user-friends me-1"></i>
                                    {{ $analytics['totalCustomers'] }} tổng cộng
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-line me-2"></i>Xu hướng doanh thu
                        </h5>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary active" onclick="loadSalesChart('7days')" id="btn-7days">7 ngày</button>
                            <button class="btn btn-outline-primary" onclick="loadSalesChart('30days')" id="btn-30days">30 ngày</button>
                            <button class="btn btn-outline-primary" onclick="loadSalesChart('12months')" id="btn-12months">12 tháng</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="salesTrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie me-2"></i>Doanh thu theo danh mục
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 250px;">
                            <canvas id="categoryRevenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Methods & Customer Tiers Row --}}
        <div class="row g-4 mb-4 d-flex">
            <div class="col-xl-4 d-flex">
                <div class="card w-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-credit-card me-2"></i>Phương thức thanh toán
                        </h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="chart-container flex-grow-1" style="height: 200px;">
                            <canvas id="paymentMethodChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 d-flex">
                <div class="card w-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-crown me-2"></i>Phân bố khách hàng
                        </h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="flex-grow-1">
                            @foreach($analytics['customerTiers'] as $tier)
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center">
                                <span class="tier-badge tier-{{ $tier['class'] }} me-2">
                                    {{ ucfirst($tier['tier']) }}
                                </span>
                                        <span>{{ $tier['count'] }} khách hàng</span>
                                    </div>
                                    <div class="progress" style="width: 100px; height: 8px;">
                                        <div class="progress-bar bg-{{ $tier['class'] }}"
                                             style="width: {{ $tier['percentage'] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 d-flex">
                <div class="card w-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt me-2"></i>Thao tác nhanh
                        </h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="d-grid gap-2 flex-grow-1">
                            <a href="{{ route('products.create') }}" class="btn btn-success quick-action-btn">
                                <i class="fas fa-plus me-2"></i>Thêm sản phẩm
                            </a>
                            <a href="{{ route('customers.create') }}" class="btn btn-info quick-action-btn">
                                <i class="fas fa-user-plus me-2"></i>Thêm khách hàng
                            </a>
                            <button class="btn btn-warning quick-action-btn" onclick="generateReport()">
                                <i class="fas fa-file-export me-2"></i>Xuất báo cáo
                            </button>
                            <button class="btn btn-secondary quick-action-btn" onclick="backupSystem()">
                                <i class="fas fa-database me-2"></i>Sao lưu dữ liệu
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-8 d-flex">
                <div class="card flex-fill">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-receipt me-2"></i>Đơn hàng gần đây
                        </h5>
                        <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt me-1"></i>Xem tất cả
                        </a>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="table-responsive flex-grow-1">
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Nhân viên</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thời gian</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($analytics['recentOrders'] as $order)
                                    <tr>
                                        <td>
                                            <a {{--href="{{ route('orders.show', $order->id) }}"--}} class="text-primary fw-bold">
                                                {{ $order->code }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($order->customer)
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar-sm me-2">
                                                        {{ strtoupper(substr($order->customer->name, 0, 1)) }}
                                                    </div>
                                                    {{ $order->customer->name }}
                                                </div>
                                            @else
                                                <span class="text-muted">Khách lẻ</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($order->user)
                                                <span class="badge badge-info">{{ $order->user->name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold">{{ number_format($order->grand_total) }}đ</td>
                                        <td>
                                <span class="badge badge-{{ $order->status === 'completed' ? 'success' : ($order->status === 'pending' ? 'warning' : 'danger') }}">
                                    @switch($order->status)
                                        @case('completed') Hoàn thành @break
                                        @case('pending') Chờ xử lý @break
                                        @case('canceled') Đã hủy @break
                                        @default {{ ucfirst($order->status) }}
                                    @endswitch
                                </span>
                                        </td>
                                        <td>
                                <span title="{{ $order->created_at->format('d/m/Y H:i:s') }}">
                                    {{ $order->created_at->diffForHumans() }}
                                </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                            Chưa có đơn hàng nào
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-star me-2"></i>Sản phẩm bán chạy
                        </h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="table-responsive flex-grow-1">
                            <table class="table table-sm mb-0">
                                <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Đã bán</th>
                                    <th>Tồn kho</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($analytics['topProducts'] as $product)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($product->thumbnail)
                                                    <img src="{{ asset('storage/' . $product->thumbnail) }}"
                                                         alt="{{ $product->name }}"
                                                         class="rounded me-2"
                                                         style="width: 30px; height: 30px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center"
                                                         style="width: 30px; height: 30px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-bold" style="font-size: 0.85rem;">{{ Str::limit($product->name, 20) }}</div>
                                                    <small class="text-muted">{{ number_format($product->revenue) }}đ</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">{{ $product->total_sold }}</span>
                                        </td>
                                        <td>
                                <span class="badge badge-{{ $product->stock > 10 ? 'success' : ($product->stock > 0 ? 'warning' : 'danger') }}">
                                    {{ $product->stock }}
                                </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">
                                            Chưa có dữ liệu
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alerts & Activity Row --}}
        <div class="row g-4">
            <div class="col-xl-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bell me-2"></i>Cảnh báo hệ thống
                        </h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="flex-grow-1">
                            @if($analytics['lowStockProducts']->count() > 0)
                                <div class="alert alert-warning">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <h6 class="mb-0">Sản phẩm sắp hết hàng ({{ $analytics['lowStockProducts']->count() }})</h6>
                                    </div>
                                    <ul class="mb-0">
                                        @foreach($analytics['lowStockProducts']->take(5) as $product)
                                            <li>
                                                <strong>{{ $product->product->name }}</strong>
                                                @if($product->variant_name)
                                                    ({{ $product->variant_name }})
                                                @endif
                                                - còn <span class="text-danger">{{ $product->stock }}</span> cái
                                            </li>
                                        @endforeach
                                        @if($analytics['lowStockProducts']->count() > 5)
                                            <li class="text-muted">... và {{ $analytics['lowStockProducts']->count() - 5 }} sản phẩm khác</li>
                                        @endif
                                    </ul>
                                </div>
                            @endif

                            @if($analytics['pendingOrders'] > 0)
                                <div class="alert alert-info">
                                    <i class="fas fa-clock me-2"></i>
                                    Có <strong>{{ $analytics['pendingOrders'] }}</strong> đơn hàng đang chờ xử lý
                                    <a href="{{ route('orders.index', ['status' => 'pending']) }}" class="btn btn-sm btn-info ms-2">
                                        Xem ngay
                                    </a>
                                </div>
                            @endif

                            @if($analytics['failedPayments'] > 0)
                                <div class="alert alert-danger">
                                    <i class="fas fa-credit-card me-2"></i>
                                    Có <strong>{{ $analytics['failedPayments'] }}</strong> giao dịch thanh toán thất bại hôm nay
                                </div>
                            @endif

                            @if($analytics['lowStockProducts']->count() == 0 && $analytics['pendingOrders'] == 0 && $analytics['failedPayments'] == 0)
                                <div class="text-center py-4">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <h6 class="text-success">Hệ thống hoạt động bình thường</h6>
                                    <p class="text-muted mb-0">Không có cảnh báo nào cần xử lý</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2"></i>Hoạt động gần đây
                        </h5>
                    </div>
                    <div class="card-body d-flex flex-column p-0">
                        <div class="flex-grow-1" style="max-height: 400px; overflow-y: auto;">
                            @forelse($analytics['recentActivities'] as $activity)
                                <div class="activity-item p-3 border-bottom">
                                    <div class="d-flex align-items-start">
                                        <div class="activity-icon me-3">
                                            @switch($activity['type'])
                                                @case('order_created')
                                                    <i class="fas fa-plus-circle text-success"></i>
                                                    @break
                                                @case('product_added')
                                                    <i class="fas fa-box text-info"></i>
                                                    @break
                                                @case('customer_registered')
                                                    <i class="fas fa-user-plus text-primary"></i>
                                                    @break
                                                @case('payment_completed')
                                                    <i class="fas fa-credit-card text-success"></i>
                                                    @break
                                                @case('user_login')
                                                    <i class="fas fa-sign-in-alt text-muted"></i>
                                                    @break
                                                @default
                                                    <i class="fas fa-info-circle text-muted"></i>
                                            @endswitch
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold mb-1">{{ $activity['description'] }}</div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">{{ $activity['user'] }}</small>
                                                <small class="text-muted">{{ $activity['time'] }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <i class="fas fa-history fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Chưa có hoạt động nào</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Global chart variables
        let salesTrendChart;
        let categoryRevenueChart;
        let paymentMethodChart;

        // Initialize charts when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            initializeSalesTrendChart();
            initializeCategoryRevenueChart();
            initializePaymentMethodChart();

            // Auto-refresh data every 5 minutes
            setInterval(refreshDashboardData, 300000);
        });

        // Sales Trend Chart
        function initializeSalesTrendChart() {
            const ctx = document.getElementById('salesTrendChart').getContext('2d');

            salesTrendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($analytics['salesChartLabels']) !!},
                    datasets: [{
                        label: 'Doanh thu (VND)',
                        data: {!! json_encode($analytics['salesChartData']) !!},
                        borderColor: '#ae8269',
                        backgroundColor: 'rgba(174, 130, 105, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                                }
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }

        // Category Revenue Chart
        function initializeCategoryRevenueChart() {
            const ctx = document.getElementById('categoryRevenueChart').getContext('2d');

            categoryRevenueChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($analytics['categoryLabels']) !!},
                    datasets: [{
                        data: {!! json_encode($analytics['categoryData']) !!},
                        backgroundColor: [
                            '#ae8269',
                            '#93614b',
                            '#978772',
                            '#a6a397',
                            '#d4c5b8',
                            '#e8dcc6'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Payment Method Chart
        function initializePaymentMethodChart() {
            const ctx = document.getElementById('paymentMethodChart').getContext('2d');

            paymentMethodChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($analytics['paymentLabels']) !!},
                    datasets: [{
                        label: 'Số lượng',
                        data: {!! json_encode($analytics['paymentData']) !!},
                        backgroundColor: [
                            '#ae8269',
                            '#6b8e23',
                            '#3498db'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Load sales chart with different periods
        function loadSalesChart(period) {
            // Update active button
            document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById('btn-' + period).classList.add('active');

            // Fetch new data
            fetch(`/dashboard/sales-chart?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    salesTrendChart.data.labels = data.labels;
                    salesTrendChart.data.datasets[0].data = data.data;
                    salesTrendChart.update();
                })
                .catch(error => {
                    console.error('Error loading sales chart:', error);
                    showAlert('Lỗi khi tải dữ liệu biểu đồ', 'error');
                });
        }

        // Quick action functions
        function generateReport() {
            PosAlert.confirm(
                'Xuất báo cáo',
                'Bạn muốn xuất báo cáo nào?',
                function() {
                    window.location.href = '/reports/generate';
                }
            );
        }

        function backupSystem() {
            PosAlert.confirm(
                'Sao lưu hệ thống',
                'Thao tác này có thể mất vài phút. Bạn có chắc chắn muốn tiếp tục?',
                function() {
                    fetch('/system/backup', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                PosAlert.success('Sao lưu thành công', 'Dữ liệu đã được sao lưu an toàn');
                            } else {
                                PosAlert.error('Sao lưu thất bại', data.message);
                            }
                        })
                        .catch(error => {
                            PosAlert.error('Lỗi hệ thống', 'Không thể thực hiện sao lưu');
                        });
                }
            );
        }

        // Refresh dashboard data
        function refreshDashboardData() {
            fetch('/dashboard/refresh')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update overview cards
                        updateOverviewCards(data.analytics);
                        console.log('Dashboard data refreshed');
                    }
                })
                .catch(error => {
                    console.error('Error refreshing dashboard:', error);
                });
        }

        function updateOverviewCards(analytics) {
            // Update sales
            document.querySelector('.overview-card h3').textContent =
                new Intl.NumberFormat('vi-VN').format(analytics.todaySales) + 'đ';

            // Update other metrics as needed
        }

        // Show alert function
        function showAlert(message, type) {
            if (typeof PosAlert !== 'undefined') {
                PosAlert[type](message);
            } else {
                alert(message);
            }
        }

        let salesChart = null;

        // Initialize chart when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initSalesChart();
            loadChartData('7days'); // Load default data
        });

        function initSalesChart() {
            const ctx = document.getElementById('salesChart');
            if (!ctx) return;

            salesChart = new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Doanh thu (VNĐ)',
                        data: [],
                        borderColor: 'rgb(139, 69, 19)',
                        backgroundColor: 'rgba(139, 69, 19, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Xu hướng doanh thu'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                                }
                            }
                        }
                    }
                }
            });
        }

        function loadChartData(period) {
            // Show loading state
            showChartLoading(true);

            fetch(`/dashboard/sales-chart?period=${period}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        updateChart(data.data);
                        updateActiveButton(period);
                    } else {
                        throw new Error(data.message || 'Lỗi không xác định');
                    }
                })
                .catch(error => {
                    console.error('Error loading chart data:', error);
                    showError('Lỗi tải dữ liệu biểu đồ: ' + error.message);
                })
                .finally(() => {
                    showChartLoading(false);
                });
        }

        function updateChart(chartData) {
            if (!salesChart || !chartData) return;

            salesChart.data.labels = chartData.labels;
            salesChart.data.datasets[0].data = chartData.data;
            salesChart.update('active');
        }

        function updateActiveButton(activePeriod) {
            // Remove active class from all buttons
            document.querySelectorAll('.period-btn').forEach(btn => {
                btn.classList.remove('active', 'btn-primary');
                btn.classList.add('btn-outline-primary');
            });

            // Add active class to clicked button
            const activeBtn = document.querySelector(`[data-period="${activePeriod}"]`);
            if (activeBtn) {
                activeBtn.classList.remove('btn-outline-primary');
                activeBtn.classList.add('btn-primary', 'active');
            }
        }

        function showChartLoading(show) {
            const loadingEl = document.getElementById('chart-loading');
            const chartEl = document.getElementById('salesChart');

            if (show) {
                if (loadingEl) loadingEl.style.display = 'block';
                if (chartEl) chartEl.style.opacity = '0.5';
            } else {
                if (loadingEl) loadingEl.style.display = 'none';
                if (chartEl) chartEl.style.opacity = '1';
            }
        }

        function showError(message) {
            // Using SweetAlert2 if available
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: message,
                    confirmButtonColor: '#8b4513'
                });
            } else {
                alert(message);
            }
        }

        // Event listeners for period buttons
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('period-btn')) {
                e.preventDefault();
                const period = e.target.dataset.period;
                loadChartData(period);
            }
        });
    </script>
@endpush
