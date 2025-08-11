@extends('layouts.app')

@section('title', 'Quản lý đơn hàng')
@section('page-title', 'Đơn hàng')

@push('styles')
    <style>
        .order-status-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #d1edff; color: #0c5460; }
        .status-canceled { background: #f8d7da; color: #721c24; }

        .payment-unpaid { background: #f8d7da; color: #721c24; }
        .payment-paid { background: #d1edff; color: #0c5460; }
        .payment-failed { background: #f5c6cb; color: #721c24; }

        .order-type-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-weight: 700;
        }

        .type-sale { background: var(--success-light); color: var(--success); }
        .type-refund { background: var(--error-light); color: var(--error); }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        {{-- Statistics Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-primary">{{ number_format($stats['total']) }}</div>
                        <div class="text-muted">Tổng đơn hàng</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-warning">{{ $stats['pending'] }}</div>
                        <div class="text-muted">Chờ xử lý</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-success">{{ $stats['completed'] }}</div>
                        <div class="text-muted">Hoàn thành</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-danger">{{ $stats['canceled'] }}</div>
                        <div class="text-muted">Đã hủy</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-info">{{ number_format($stats['total_revenue']) }}đ</div>
                        <div class="text-muted">Doanh thu</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-secondary">{{ $stats['refund_orders'] }}</div>
                        <div class="text-muted">Hoàn trả</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-receipt me-2"></i>Danh sách đơn hàng
                        </h5>
                        <div class="btn-group">
                            <a href="{{ route('pos') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Tạo đơn mới
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Filters --}}
                        <form method="GET" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <input type="text" name="search"
                                       class="form-control"
                                       placeholder="Tìm mã đơn, khách hàng..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                    <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Đã hủy</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="payment_status" class="form-select">
                                    <option value="">Thanh toán</option>
                                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
                                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                                    <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Thất bại</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="type" class="form-select">
                                    <option value="">Loại đơn</option>
                                    <option value="sale" {{ request('type') == 'sale' ? 'selected' : '' }}>Bán hàng</option>
                                    <option value="refund" {{ request('type') == 'refund' ? 'selected' : '' }}>Hoàn trả</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>

                        {{-- Orders Table --}}
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Mã đơn hàng</th>
                                    <th>Khách hàng</th>
                                    <th>Sản phẩm</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thanh toán</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div>
                                                    <div class="fw-bold">{{ $order->code }}</div>
                                                    <small class="text-muted">#{{ $order->id }}</small>
                                                </div>
                                                <span class="order-type-badge type-{{ $order->type }}">
                                                {{ $order->type === 'sale' ? 'Bán' : 'Hoàn trả' }}
                                            </span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($order->customer)
                                                <div class="fw-bold">{{ $order->customer->name }}</div>
                                                <small class="text-muted">{{ $order->customer->phone }}</small>
                                            @else
                                                <span class="text-muted">Khách lẻ</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $order->items->count() }} sản phẩm</div>
                                            <small class="text-muted">{{ $order->items->sum('quantity') }} món</small>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-{{ $order->type === 'refund' ? 'danger' : 'success' }}">
                                                {{ $order->type === 'refund' ? '-' : '' }}{{ number_format($order->grand_total) }}đ
                                            </div>
                                            @if($order->discount_total > 0)
                                                <small class="text-warning">-{{ number_format($order->discount_total) }}đ</small>
                                            @endif
                                        </td>
                                        <td>
                                        <span class="order-status-badge status-{{ $order->status }}">
                                            @switch($order->status)
                                                @case('pending') Chờ xử lý @break
                                                @case('completed') Hoàn thành @break
                                                @case('canceled') Đã hủy @break
                                            @endswitch
                                        </span>
                                        </td>
                                        <td>
                                        <span class="order-status-badge payment-{{ $order->payment_status }}">
                                            @switch($order->payment_status)
                                                @case('unpaid') Chưa Thanh Toán @break
                                                @case('paid') Đã Thanh Toán @break
                                                @case('failed') Thất bại @break
                                            @endswitch
                                        </span>
                                            <div>
                                                <small class="text-muted">
                                                    @switch($order->payment_method)
                                                        @case('vnpay') VNPay @break
                                                        @case('cod') COD @break
                                                        @case('cash_at_counter') Tiền mặt @break
                                                    @endswitch
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>{{ $order->created_at->format('d/m/Y H:i') }}</div>
                                            <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
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
                                        <td colspan="8" class="text-center py-5">
                                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">Chưa có đơn hàng nào</h6>
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
