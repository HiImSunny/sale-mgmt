@extends('layouts.app')

@section('title', 'Chi tiết khách hàng - ' . $customer->name)
@section('page-title', 'Chi tiết khách hàng')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/chart.js"></link>
    <style>
        .customer-header {
            background: linear-gradient(135deg, var(--accent-primary), var(--brown-medium));
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .customer-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(50%, -50%);
        }

        .customer-avatar-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px var(--shadow-light);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px var(--shadow-medium);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .order-status-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">


        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="customer-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <div class="customer-avatar-large me-4">
                                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <h2 class="mb-1">{{ $customer->name }}</h2>
                                        <div class="d-flex align-items-center gap-3 mb-2">
                                        <span class="tier-badge tier-{{ $customer->customer_tier }} d-inline-block">
                                            {{ ucfirst($customer->customer_tier) }} Member
                                        </span>
                                            @if($customer->is_vip)
                                                <span class="vip-badge">VIP</span>
                                            @endif
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-auto">
                                                <i class="fas fa-phone me-1"></i>{{ $customer->phone }}
                                            </div>
                                            @if($customer->email)
                                                <div class="col-auto">
                                                    <i class="fas fa-envelope me-1"></i>{{ $customer->email }}
                                                </div>
                                            @endif
                                            @if($customer->birthday)
                                                <div class="col-auto">
                                                    <i class="fas fa-birthday-cake me-1"></i>{{ $customer->birthday->format('d/m/Y') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <div class="btn-group">
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-light">
                                            <i class="fas fa-edit me-2"></i>Chỉnh sửa
                                        </a>

                                        <a href="{{ route('customers.index') }}" class="btn btn-light">
                                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            @if($customer->address)
                                <div class="col-md-6">
                                    <h6><i class="fas fa-map-marker-alt me-2"></i>Địa chỉ</h6>
                                    <p class="text-muted">{{ $customer->address }}</p>
                                </div>
                            @endif
                            @if($customer->notes)
                                <div class="col-md-6">
                                    <h6><i class="fas fa-sticky-note me-2"></i>Ghi chú</h6>
                                    <p class="text-muted">{{ $customer->notes }}</p>
                                </div>
                            @endif
                            <div class="col-12 mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Tham gia ngày {{ $customer->created_at->format('d/m/Y') }}
                                    ({{ $customer->created_at->diffForHumans() }})
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <div class="stat-value text-primary">{{ $stats['total_orders'] }}</div>
                        <div class="text-muted">Tổng đơn hàng</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <div class="stat-value text-success">{{ number_format($stats['total_spent']) }}đ</div>
                        <div class="text-muted">Tổng chi tiêu</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <div class="stat-value text-info">{{ number_format($stats['avg_order_value']) }}đ</div>
                        <div class="text-muted">Giá trị đơn TB</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <div class="stat-value text-warning">{{ $stats['completed_orders'] }}</div>
                        <div class="text-muted">Đơn hoàn thành</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts and Top Products --}}
        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-area me-2"></i>Chi tiêu theo tháng (12 tháng gần nhất)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="spendingChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-star me-2"></i>Sản phẩm yêu thích
                        </h5>
                    </div>
                    <div class="card-body">
                        @forelse($topProducts as $product)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="fw-bold">{{ $product->name }}</div>
                                    <small class="text-muted">{{ $product->sku_snapshot }}</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-primary">{{ $product->total_quantity }}</div>
                                    <small class="text-muted">{{ number_format($product->total_spent) }}đ</small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                <div>Chưa có sản phẩm nào được mua</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Order History --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2"></i>Lịch sử mua hàng
                        </h5>
                        <div class="d-flex gap-2">
                            <span class="badge bg-primary">{{ $stats['completed_orders'] }} hoàn thành</span>
                            <span class="badge bg-warning">{{ $stats['pending_orders'] }} chờ xử lý</span>
                            <span class="badge bg-danger">{{ $stats['canceled_orders'] }} đã hủy</span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Mã đơn hàng</th>
                                    <th>Ngày đặt</th>
                                    <th>Sản phẩm</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Nhân viên</th>
                                    <th>Thao tác</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $order->code }}</div>
                                            <small class="text-muted">#{{ $order->id }}</small>
                                        </td>
                                        <td>
                                            <div>{{ $order->created_at->format('d/m/Y H:i') }}</div>
                                            <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <div>
                                                {{ $order->orderItems->count() }} sản phẩm
                                                <button class="btn btn-sm btn-link p-0" onclick="showOrderItems({{ $order->id }})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <small class="text-muted">
                                                {{ $order->orderItems->sum('quantity') }} món
                                            </small>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-success">{{ number_format($order->grand_total) }}đ</div>
                                            @if($order->discount_total > 0)
                                                <small class="text-danger">
                                                    -{{ number_format($order->discount_total) }}đ
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                        <span class="order-status-badge
                                            @switch($order->status)
                                                @case('completed') bg-success @break
                                                @case('pending') bg-warning @break
                                                @case('canceled') bg-danger @break
                                                @default bg-secondary
                                            @endswitch">
                                            @switch($order->status)
                                                @case('completed') Hoàn thành @break
                                                @case('pending') Chờ xử lý @break
                                                @case('canceled') Đã hủy @break
                                                @default {{ ucfirst($order->status) }}
                                            @endswitch
                                        </span>
                                        </td>
                                        <td>
                                            @if($order->user)
                                                <div>{{ $order->user->name }}</div>
                                                <small class="text-muted">{{ ucfirst($order->user->role) }}</small>
                                            @else
                                                <span class="text-muted">Hệ thống</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('orders.show', $order) }}"
                                                   class="btn btn-outline-info" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($order->status === 'completed')
                                                    <a href="{{ route('orders.invoice', $order) }}"
                                                       class="btn btn-outline-primary" title="In hóa đơn" target="_blank">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">Chưa có đơn hàng nào</h6>
                                            <p class="text-muted">Khách hàng chưa thực hiện giao dịch nào</p>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @if($orders->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $orders->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Spending Chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('spendingChart').getContext('2d');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($monthlySpending['labels']) !!},
                    datasets: [{
                        label: 'Chi tiêu (VND)',
                        data: {!! json_encode($monthlySpending['data']) !!},
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
                    }
                }
            });
        });

        function showOrderItems(orderId) {
            // Implementation for showing order items in modal
            console.log('Show order items for order:', orderId);
        }


        function addNote() {
            // Implementation for adding note
            console.log('Add customer note');
        }

        function deleteCustomer() {
            PosAlert.confirm(
                'Xóa khách hàng?',
                'Bạn có chắc chắn muốn xóa khách hàng này? Thao tác này không thể hoàn tác.',
                function() {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("customers.destroy", $customer) }}';
                    form.innerHTML = `
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
            `;
                    document.body.appendChild(form);
                    form.submit();
                }
            );
        }
    </script>
@endpush
