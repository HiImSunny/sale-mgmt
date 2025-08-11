@extends('layouts.app')

@section('title', 'Quản lý khách hàng')
@section('page-title', 'Khách hàng')

@push('styles')
    <style>
        .stats-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px var(--shadow-light);
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px var(--shadow-medium);
        }

        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

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

        .vip-badge {
            background: linear-gradient(45deg, #ff6b6b, #ff8e53);
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(255, 107, 107, 0.3);
        }

        .search-box {
            background: var(--bg-white);
            border: 2px solid var(--border-light);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .search-box:focus {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 0.2rem var(--accent-warm);
        }
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
                        <div class="text-muted">Tổng khách hàng</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-secondary">{{ $stats['bronze'] }}</div>
                        <div class="text-muted">Bronze</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-info">{{ $stats['silver'] }}</div>
                        <div class="text-muted">Silver</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-warning">{{ $stats['gold'] }}</div>
                        <div class="text-muted">Gold</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold" style="color: #7b1fa2;">{{ $stats['platinum'] }}</div>
                        <div class="text-muted">Platinum</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-danger">{{ $stats['vip'] }}</div>
                        <div class="text-muted">VIP</div>
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
                            <i class="fas fa-users me-2"></i>Danh sách khách hàng
                        </h5>
                        <a href="{{ route('customers.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Thêm khách hàng
                        </a>
                    </div>

                    <div class="card-body">
                        {{-- Filters --}}
                        <form method="GET" class="row g-3 mb-4">
                            <div class="col-md-4">
                                <input type="text" name="search"
                                       class="form-control search-box"
                                       placeholder="Tìm kiếm tên, SĐT, email..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="tier" class="form-select">
                                    <option value="">Tất cả tier</option>
                                    <option value="bronze" {{ request('tier') == 'bronze' ? 'selected' : '' }}>Bronze</option>
                                    <option value="silver" {{ request('tier') == 'silver' ? 'selected' : '' }}>Silver</option>
                                    <option value="gold" {{ request('tier') == 'gold' ? 'selected' : '' }}>Gold</option>
                                    <option value="platinum" {{ request('tier') == 'platinum' ? 'selected' : '' }}>Platinum</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="vip" class="form-select">
                                    <option value="">Tất cả</option>
                                    <option value="1" {{ request('vip') == '1' ? 'selected' : '' }}>VIP</option>
                                    <option value="0" {{ request('vip') == '0' ? 'selected' : '' }}>Không VIP</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="sort" class="form-select">
                                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Ngày tạo</option>
                                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Tên</option>
                                    <option value="total_spent" {{ request('sort') == 'total_spent' ? 'selected' : '' }}>Tổng chi tiêu</option>
                                    <option value="total_orders" {{ request('sort') == 'total_orders' ? 'selected' : '' }}>Số đơn hàng</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-search me-1"></i>Tìm kiếm
                                </button>
                            </div>
                        </form>

                        {{-- Customer Table --}}
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Khách hàng</th>
                                    <th>Liên hệ</th>
                                    <th>Tier</th>
                                    <th>Đơn hàng</th>
                                    <th>Tổng chi tiêu</th>
                                    <th>Ngày tham gia</th>
                                    <th>Thao tác</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($customers as $customer)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="customer-avatar me-3"
                                                     style="background: linear-gradient(45deg, {{ $customer->is_vip ? '#ff6b6b, #ff8e53' : '#' . substr(md5($customer->name), 0, 6) . ', #' . substr(md5($customer->name), 6, 6) }});">
                                                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $customer->name }}</div>
                                                    @if($customer->is_vip)
                                                        <span class="vip-badge">VIP</span>
                                                    @endif
                                                    @if($customer->birthday)
                                                        <small class="text-muted d-block">
                                                            <i class="fas fa-birthday-cake me-1"></i>
                                                            {{ $customer->birthday->format('d/m/Y') }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $customer->phone }}</div>
                                            @if($customer->email)
                                                <small class="text-muted">{{ $customer->email }}</small>
                                            @endif
                                        </td>
                                        <td>
                                        <span class="tier-badge tier-{{ $customer->customer_tier }}">
                                            {{ ucfirst($customer->customer_tier) }}
                                        </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="fw-bold">{{ $customer->total_orders }}</div>
                                            <small class="text-muted">đơn hàng</small>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-success">{{ number_format($customer->total_spent) }}đ</div>
                                            @if($customer->total_orders > 0)
                                                <small class="text-muted">
                                                    TB: {{ number_format($customer->total_spent / $customer->total_orders) }}đ/đơn
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $customer->created_at->format('d/m/Y') }}</div>
                                            <small class="text-muted">{{ $customer->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('customers.show', $customer) }}"
                                                   class="btn btn-outline-info" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('customers.edit', $customer) }}"
                                                   class="btn btn-outline-primary" title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger"
                                                        onclick="deleteCustomer({{ $customer->id }})" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">Chưa có khách hàng nào</h6>
                                            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus me-2"></i>Thêm khách hàng đầu tiên
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @if($customers->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $customers->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function deleteCustomer(customerId) {
            PosAlert.confirm(
                'Xóa khách hàng?',
                'Bạn có chắc chắn muốn xóa khách hàng này? Thao tác này không thể hoàn tác.',
                function() {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/customers/${customerId}`;
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
