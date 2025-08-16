
@extends('layouts.app')

@section('title', 'Sao lưu & Khôi phục')
@section('page-title', 'Sao lưu & Khôi phục')

@push('styles')
    <style>
        /* ====== STAT CARDS (giống trang Sản phẩm) ====== */
        .stats-card {
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,.05);
            transition: .2s;
        }
        .stats-card:hover { transform: translateY(-2px); }

        .stats-card .display-6 {
            line-height: 1;
        }

        /* ====== BACKUP TABLE ====== */
        .backup-table .badge {
            font-size: .75rem;
            font-weight: 500;
        }
        .badge-full   { background:#d1ecf1;color:#0c5460;border:1px solid #bee5eb; }
        .badge-db     { background:#d4edda;color:#155724;border:1px solid #c3e6cb; }

        /* ====== EMPTY STATE ====== */
        .empty-state {
            text-align:center;padding:80px 20px;color:#6c757d;
        }
        .empty-state i { font-size:3rem;margin-bottom:1rem;opacity:.4; }

        /* ====== PROGRESS OVERLAY ====== */
        .progress-overlay{
            position:fixed;top:0;left:0;width:100%;height:100%;
            background:rgba(0,0,0,.7);display:none;
            justify-content:center;align-items:center;z-index:9999;
        }
        .progress-content{
            background:#fff;padding:40px;border-radius:8px;min-width:300px;text-align:center;
        }

        /* ====== CREATE BACKUP BUTTONS ====== */
        .create-backup-buttons{display:flex;gap:10px;flex-wrap:wrap;justify-content:center;}
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>

            </div>
            <a href="{{ route('settings.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>Quay lại Settings
            </a>
        </div>

        <!-- STATS (dùng card giống trang sản phẩm) -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-primary">{{ $backups->count() }}</div>
                        <div class="text-muted">Tổng số backup</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-success">{{ $backups->where('type','full')->count() }}</div>
                        <div class="text-muted">Backup toàn bộ</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-info">{{ $backups->where('type','database')->count() }}</div>
                        <div class="text-muted">Backup database</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 col-sm-6">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-danger">{{ number_format($backups->sum('size')/1024/1024,1) }}</div>
                        <div class="text-muted">MB tổng dung lượng</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FLASH MESSAGES -->
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

        <!-- BACKUP LIST CARD -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Danh sách Backup
                    @if($backups->count() > 0)
                        <span class="badge bg-secondary ms-2">{{ $backups->count() }} files</span>
                    @endif
                </h5>

                <div class="col-md-4">
                    <div class="create-backup-buttons">
                        <form action="{{ route('backup.createFull') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary"
                                    onclick="showProgress('Đang tạo backup toàn bộ...')">
                                <i class="fas fa-archive me-2"></i>Backup Toàn bộ
                            </button>
                        </form>
                        <form action="{{ route('backup.createDB') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success"
                                    onclick="showProgress('Đang tạo backup database...')">
                                <i class="fas fa-database me-2"></i>Backup Database
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            @if($backups->count() > 0)
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 backup-table">
                            <thead class="table-light">
                            <tr>
                                <th>Tên file</th>
                                <th>Loại</th>
                                <th>Kích thước</th>
                                <th>Ngày tạo</th>
                                <th width="120">Thao tác</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($backups as $backup)
                                <tr>
                                    <td class="align-middle fw-semibold">{{ $backup['name'] }}</td>
                                    <td class="align-middle">
                                    <span class="badge {{ $backup['type']==='full' ? 'badge-full' : 'badge-db' }}">
                                        <i class="fas {{ $backup['type']==='full' ? 'fa-archive' : 'fa-database' }} me-1"></i>
                                        {{ $backup['type']==='full' ? 'Toàn bộ' : 'Database' }}
                                    </span>
                                    </td>
                                    <td class="align-middle">{{ number_format($backup['size']/1024/1024,2) }} MB</td>
                                    <td class="align-middle">
                                        {{ \Carbon\Carbon::createFromTimestamp($backup['created'])->setTimezone(config('app.timezone'))->format('d/m/Y H:i:s') }}<br>
                                        <small class="text-muted">{{ \Carbon\Carbon::createFromTimestamp($backup['created'])->setTimezone(config('app.timezone'))->diffForHumans() }}</small>
                                    </td>
                                    <td class="align-middle">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('backup.download',['filename'=>$backup['name']]) }}"
                                               class="btn btn-outline-success btn-sm" target="_blank" title="Tải về">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-warning btn-sm"
                                                    onclick="confirmRestore('{{ $backup['name'] }}','{{ $backup['type'] }}')"
                                                    title="Khôi phục">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                    onclick="confirmDelete('{{ $backup['name'] }}')" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-archive"></i>
                    <h5 class="mb-3">Chưa có backup nào</h5>
                    <p class="mb-4">Tạo backup đầu tiên để bảo vệ dữ liệu quan trọng của bạn</p>

                    <form action="{{ route('backup.createFull') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary"
                                onclick="showProgress('Đang tạo backup toàn bộ...')">
                            <i class="fas fa-archive me-2"></i>Tạo Backup đầu tiên
                        </button>
                    </form>
                </div>
            @endif

            @if($backups->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $backups->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- PROGRESS OVERLAY -->
    <div id="progressOverlay" class="progress-overlay">
        <div class="progress-content">
            <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h5 id="progressText">Đang xử lý...</h5>
            <p class="text-muted mb-0">Vui lòng đợi, quá trình này có thể mất vài phút</p>
        </div>
    </div>

    <script>
        function showProgress(message) {
            document.getElementById('progressText').textContent = message;
            document.getElementById('progressOverlay').style.display = 'flex';
        }

        function confirmRestore(fileName, type) {
            document.getElementById('restoreFileName').textContent = fileName;
            document.getElementById('restoreFileType').textContent = type === 'full' ? 'Backup Toàn bộ' : 'Backup Database';
            document.getElementById('restoreBackupFile').value = fileName;

            const modal = new bootstrap.Modal(document.getElementById('restoreModal'));
            modal.show();
        }

        function confirmDelete(fileName) {
            document.getElementById('deleteFileName').textContent = fileName;
            document.getElementById('deleteBackupFile').value = fileName;

            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        // Hide progress overlay when page loads
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('progressOverlay').style.display = 'none';
        });
    </script>
@endsection
