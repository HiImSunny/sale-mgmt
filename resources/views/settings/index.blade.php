@extends('layouts.app')

@section('title', 'Cài đặt')
@section('page-title', 'Cài đặt')

@push('styles')
    <style>
        .settings-container{margin:20px 0;}

        .settings-card{
            background:#fff;border:1px solid #dee2e6;border-radius:8px;
            box-shadow:0 2px 4px rgba(0,0,0,.05);margin-bottom:20px;transition:.2s;
        }
        .settings-card:hover{border-color:#0d6efd;box-shadow:0 4px 8px rgba(0,0,0,.1);}

        .setting-section{padding:20px;border-bottom:1px solid #dee2e6;}
        .setting-section:last-child{border-bottom:none;}
        .setting-section h6{color:#495057;font-weight:600;margin-bottom:15px;display:flex;align-items:center;gap:8px;}

        .form-group{margin-bottom:20px;}
        .form-label{font-weight:500;color:#495057;margin-bottom:5px;}
        .input-group-text{background:#f8f9fa;border-color:#ced4da;}

        .rank-input-group{display:flex;align-items:center;gap:15px;background:#f8f9fa;padding:15px;border-radius:8px;margin-bottom:10px;}
        .rank-icon{
            width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;
            color:#fff;font-size:18px;
        }
        .rank-regular{background:#6c757d;}
        .rank-bronze{background:#CD7F32;}
        .rank-silver{background:#C0C0C0;}
        .rank-gold{background:#FFD700;}
        .rank-platinum{background:#E5E4E2;color:#333;}

        .discount-config{display:flex;gap:10px;align-items:end;}
        .discount-type-select{min-width:150px;}
        .discount-value-input{min-width:120px;}

        .help-text{font-size:.875rem;color:#6c757d;margin-top:5px;}
        .sticky-sidebar{position:sticky;top:20px;}

        /* Quick-backup box bỏ gradient */
        .backup-quick-actions{
            background:#f8f9fa;border:1px solid #dee2e6;border-radius:8px;
            padding:20px;margin-bottom:20px;
        }
        .backup-quick-actions h6{margin-bottom:15px;}

        /* Stats trong sidebar */
        .sidebar-stats .stat-item{text-align:center;margin-bottom:10px;}
        .sidebar-stats .stat-number{font-size:1.25rem;font-weight:bold;color:#0d6efd;}
        .sidebar-stats .stat-label{font-size:.875rem;color:#6c757d;}
    </style>
@endpush

@section('content')
    <div class="container-fluid settings-container">

        {{-- MESSAGES --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('settings.update') }}" method="POST" id="settings-form">
            @csrf

            <div class="row">
                {{-- MAIN COLUMN --}}
                <div class="col-lg-8">

                    {{-- 1- CÀI ĐẶT CHUNG (ĐƯA LÊN TRƯỚC) --}}
                    <div class="settings-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Cài đặt chung</h5>
                        </div>
                        <div class="setting-section">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tên cửa hàng</label>
                                    <input type="text" class="form-control" name="site_name"
                                           value="{{ config('app_settings.general.site_name') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email liên hệ</label>
                                    <input type="email" class="form-control" name="contact_email"
                                           value="{{ config('app_settings.general.contact_email') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="text" class="form-control" name="contact_phone"
                                           value="{{ config('app_settings.general.contact_phone') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Địa chỉ</label>
                                    <input type="text" class="form-control" name="address"
                                           value="{{ config('app_settings.general.address') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2- CHƯƠNG TRÌNH KHÁCH HÀNG THÂN THIẾT --}}
                    <div class="settings-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-crown me-2"></i>Chương trình khách hàng thân thiết</h5>
                        </div>

                        {{-- ENABLE / DISABLE --}}
                        <div class="setting-section">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                       name="loyalty_enabled" value="1"
                                    {{ config('app_settings.loyalty.enabled') ? 'checked' : '' }}>
                                <label class="form-check-label"><strong>Bật chương trình loyalty</strong></label>
                            </div>
                            <div class="help-text">Cho phép khách hàng tích điểm và nhận ưu đãi theo 4 hạng: Bronze, Silver, Gold, Platinum</div>
                        </div>

                        {{-- RANK REQUIREMENTS --}}
                        <div class="setting-section">
                            <h6><i class="fas fa-chart-line"></i>Điều kiện nâng hạng</h6>
                            <div class="row">
                                @foreach(['bronze','silver','gold','platinum'] as $level)
                                    @php
                                        $label = ucfirst($level);
                                        $field = "rank_{$level}_min";
                                        $value = config("app_settings.loyalty.ranks.{$level}_min_amount");
                                    @endphp
                                    <div class="col-md-6 col-lg-3 mb-3">
                                        <label class="form-label">{{ $label }} (VNĐ)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₫</span>
                                            <input type="number" class="form-control" name="{{ $field }}"
                                                   value="{{ $value }}" min="0" step="100000">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- DISCOUNT SETTINGS --}}
                        <div class="setting-section">
                            <h6><i class="fas fa-percent"></i>Mức giảm giá theo hạng</h6>
                            @foreach(['regular','bronze','silver','gold','platinum'] as $rank)
                                @php
                                    $rankCfg   = config("app_settings.loyalty.discounts.$rank") ?? [];
                                    $type      = $rankCfg['type']  ?? 'percent';
                                    $val       = $rankCfg['value'] ?? 0;
                                @endphp
                                <div class="rank-input-group">
                                    <div class="rank-icon rank-{{ $rank }}">
                                        @switch($rank)
                                            @case('regular')   <i class="fas fa-user"></i>@break
                                            @case('platinum')  <i class="fas fa-crown"></i>@break
                                            @default           <i class="fas fa-medal"></i>
                                        @endswitch
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong class="text-capitalize">
                                            {{ $rank === 'regular' ? 'Khách hàng thường' : ucfirst($rank) }}
                                        </strong>
                                    </div>
                                    <div class="discount-config">
                                        <select class="form-select discount-type-select" name="discount_{{ $rank }}_type">
                                            <option value="percent" {{ $type==='percent' ? 'selected':'' }}>Phần trăm (%)</option>
                                            <option value="fixed"   {{ $type==='fixed'   ? 'selected':'' }}>Số tiền (VNĐ)</option>
                                        </select>
                                        <input type="number" class="form-control discount-value-input"
                                               name="discount_{{ $rank }}_value"
                                               value="{{ $val }}" min="0" step="0.1">
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="setting-section">
                            <h6><i class="fas fa-gift"></i>Tích điểm</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tỷ lệ tích điểm</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="points_rate"
                                               value="{{ config('app_settings.loyalty.rewards.points_rate') }}" min="0">
                                        <span class="input-group-text">điểm</span>
                                    </div>
                                    <div class="help-text">Số điểm tích được cho mỗi 100.000 VNĐ</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Giá trị quy đổi điểm</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="points_value"
                                               value="{{ config('app_settings.loyalty.rewards.points_value') }}" min="0" step="100">
                                        <span class="input-group-text">₫</span>
                                    </div>
                                    <div class="help-text">Giá trị VNĐ của 1 điểm khi quy đổi</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SIDEBAR --}}
                <div class="col-lg-4">
                    <div class="sticky-sidebar">

                        <div class="backup-quick-actions">
                            <h6><i class="fas fa-database me-2"></i>Sao lưu và khôi phục</h6>
                            <p class="mb-3">Sao lưu trước khi thay đổi để đảm bảo an toàn</p>
                            <div class="d-grid gap-2">
                                <a href="{{ route('backup.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-archive me-2"></i>Quản lý Sao lưu
                                </a>
                            </div>
                        </div>

                        <div class="settings-card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin hệ thống</h6>
                            </div>
                            <div class="card-body p-3 sidebar-stats">
                                <div class="stat-item">
                                    <div class="stat-number">{{ config('app_settings.general.site_name') ?: '—' }}</div>
                                    <div class="stat-label">Tên cửa hàng</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">{{ config('app_settings.general.contact_email') ?: '—' }}</div>
                                    <div class="stat-label">Email liên hệ</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">{{ config('app_settings.general.contact_phone') ?: '—' }}</div>
                                    <div class="stat-label">SĐT liên hệ</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">{{ config('app_settings.loyalty.enabled') ? 'BẬT' : 'TẮT' }}</div>
                                    <div class="stat-label">Loyalty</div>
                                </div>
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        Cập nhật lần cuối: {{ now()->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- SAVE BUTTON --}}
                        <div class="settings-card">
                            <div class="card-body p-3">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i>Lưu cài đặt
                                    </button>
                                </div>
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>Thay đổi sẽ có hiệu lực ngay lập tức
                                    </small>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
