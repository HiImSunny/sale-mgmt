@extends('layouts.app')

@section('title', 'Chi tiết người dùng - ' . $user->name)
@section('page-title', 'Chi tiết người dùng')

@push('styles')
    <style>
        .user-avatar-large {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .info-card {
            border-left: 4px solid #007bff;
            background: #f8f9fa;
        }

        .role-display {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #007bff;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                @if($user->role == 'seller')
                <!-- Seller Stats -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-chart-line me-2"></i>Thống kê nhân viên
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="stat-item">
                                    <div class="stat-number">{{ $stats['orders_processed'] ?? 0 }}</div>
                                    <div class="text-muted">Đơn đã xử lý</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-item">
                                    <div class="stat-number">{{ number_format($stats['revenue_generated'] ?? 0) }}đ</div>
                                    <div class="text-muted">Doanh thu tạo ra</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-item">
                                    <div class="stat-number">{{ $stats['customers_served'] ?? 0 }}</div>
                                    <div class="text-muted">Khách đã phục vụ</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Activity Log -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-history me-2"></i>Nhật ký hoạt động
                        </h6>
                    </div>
                    <div class="card-body">
                        @if(isset($activities) && $activities->count() > 0)
                            <div class="timeline">
                                @foreach($activities as $activity)
                                    <div class="d-flex mb-3">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center"
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="fw-medium">{{ $activity->description }}</div>
                                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="fas fa-history fa-2x text-muted mb-2"></i>
                                <p class="text-muted">Chưa có hoạt động nào được ghi nhận</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        @if($user->avatar)
                            <img src="{{ Storage::url($user->avatar) }}"
                                 alt="{{ $user->name }}" class="user-avatar-large mb-3">
                        @else
                            <div class="user-avatar-large bg-light d-flex align-items-center justify-content-center mx-auto mb-3">
                                <i class="fas fa-user fa-3x text-muted"></i>
                            </div>
                        @endif

                        <h4 class="mb-2">{{ $user->name }}</h4>
                        <p class="text-muted mb-3">{{ $user->email }}</p>

                        <div class="role-display role-{{ $user->role }} mb-3">
                            @switch($user->role)
                                @case('admin')
                                    <span>Quản trị viên</span>
                                    @break
                                @case('seller')
                                    <span>Nhân viên bán hàng</span>
                                    @break
                            @endswitch
                        </div>

                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>Chỉnh sửa
                            </a>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Thông tin tài khoản
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="info-card p-3 mb-3">
                            <div class="row">
                                <div class="col-sm-4"><strong>ID:</strong></div>
                                <div class="col-sm-8"><code>{{ $user->id }}</code></div>
                            </div>
                        </div>

                        <div class="info-card p-3 mb-3">
                            <div class="row">
                                <div class="col-sm-4"><strong>Email:</strong></div>
                                <div class="col-sm-8">{{ $user->email }}</div>
                            </div>
                        </div>

                        <div class="info-card p-3 mb-3">
                            <div class="row">
                                <div class="col-sm-4"><strong>Ngày tạo:</strong></div>
                                <div class="col-sm-8">
                                    {{ $user->created_at->format('d/m/Y H:i:s') }}
                                    <br><small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="info-card p-3">
                            <div class="row">
                                <div class="col-sm-4"><strong>Cập nhật:</strong></div>
                                <div class="col-sm-8">
                                    {{ $user->updated_at->format('d/m/Y H:i:s') }}
                                    <br><small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
