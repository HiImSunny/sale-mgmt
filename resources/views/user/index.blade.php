@extends('layouts.app')

@section('title', 'Quản lý người dùng')
@section('page-title', 'Người dùng')

@push('styles')
    <style>
        .user-avatar {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
        }

        .role-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .role-admin {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .role-seller {
            background-color: #d4edda;
            color: #155724;
        }

    </style>
@endpush

@section('content')
    <div class="container-fluid">

        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-4">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-primary">{{ $stats['total'] }}</div>
                        <div class="text-muted">Tổng người dùng</div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-success">{{ $stats['admin'] }}</div>
                        <div class="text-muted">Quản trị viên</div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="display-6 fw-bold text-info">{{ $stats['seller'] }}</div>
                        <div class="text-muted">Nhân viên bán hàng</div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Users Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>Danh sách người dùng
                        </h5>

                        <div class="d-flex gap-2">
                            <a href="{{ route('users.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Thêm người dùng
                            </a>
                            <button type="button" class="btn btn-success" onclick="exportUsers()">
                                <i class="fas fa-file-excel me-2"></i>Xuất Excel
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()"
                                    style="display: none;"
                                    id="bulk-delete-btn">
                                <i class="fas fa-trash"></i> Xóa đã chọn
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <form method="GET" class="row g-3 mb-4">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search"
                                       placeholder="Tìm kiếm người dùng..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="role">
                                    <option value="">Tất cả vai trò</option>
                                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Quản trị viên</option>
                                    <option value="seller" {{ request('role') == 'seller' ? 'selected' : '' }}>Nhân viên</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-50">
                                        <i class="fas fa-search"></i> Tìm kiếm
                                    </button>
                                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary w-50">
                                        <i class="fas fa-undo"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>

                        @if($users->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" id="select-all" class="form-check-input">
                                        </th>
                                        <th>Người dùng</th>
                                        <th>Email</th>
                                        <th>Vai trò</th>
                                        <th>Ngày tạo</th>
                                        <th width="120">Thao tác</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input user-checkbox"
                                                       value="{{ $user->id }}">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($user->avatar)
                                                        <img src="{{ Storage::url($user->avatar) }}"
                                                             alt="{{ $user->name }}" class="user-avatar me-3">
                                                    @else
                                                        <div class="user-avatar me-3 bg-light d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-user text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-1">{{ $user->name }}</h6>
                                                        <small class="text-muted">#{{ $user->id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-primary">{{ $user->email }}</span>
                                            </td>
                                            <td>
                                            <span class="role-badge role-{{ $user->role }}">
                                                @switch($user->role)
                                                    @case('admin')
                                                        Quản trị viên
                                                        @break
                                                    @case('seller')
                                                        Nhân viên bán hàng
                                                        @break
                                                @endswitch
                                            </span>
                                            </td>
                                            <td>
                                                <div>{{ $user->created_at->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('users.show', $user) }}"
                                                       class="btn btn-outline-info btn-sm" title="Xem">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('users.edit', $user) }}"
                                                       class="btn btn-outline-primary btn-sm" title="Sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($user->id !== auth()->id())
                                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                                onclick="deleteUser({{ $user->id }})" title="Xóa">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="card-footer">
                                {{ $users->appends(request()->query())->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Chưa có người dùng nào</h5>
                                <p class="text-muted mb-4">Thêm người dùng đầu tiên để bắt đầu</p>
                                <a href="{{ route('users.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Thêm người dùng đầu tiên
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Select all functionality
        document.getElementById('select-all').addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            const bulkDeleteBtn = document.getElementById('bulk-delete-btn');

            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });

            bulkDeleteBtn.style.display = this.checked && checkboxes.length > 0 ? 'inline-block' : 'none';
        });

        // Individual checkbox change
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
                const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
                const selectAllCheckbox = document.getElementById('select-all');

                bulkDeleteBtn.style.display = checkedBoxes.length > 0 ? 'inline-block' : 'none';
                selectAllCheckbox.checked = checkedBoxes.length === document.querySelectorAll('.user-checkbox').length;
            });
        });

        // Delete user
        function deleteUser(userId) {
            if (confirm('Bạn có chắc chắn muốn xóa người dùng này?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/users/${userId}`;

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';

                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = '{{ csrf_token() }}';

                form.appendChild(methodInput);
                form.appendChild(tokenInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Bulk delete
        function bulkDelete() {
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');

            if (checkedBoxes.length === 0) {
                alert('Vui lòng chọn ít nhất một người dùng để xóa');
                return;
            }

            if (confirm(`Bạn có chắc chắn muốn xóa ${checkedBoxes.length} người dùng đã chọn?`)) {
                const userIds = Array.from(checkedBoxes).map(cb => cb.value);

                fetch('{{ route("users.bulk-delete") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        user_ids: userIds
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Có lỗi xảy ra khi xóa người dùng');
                        }
                    });
            }
        }

        // Export users
        function exportUsers() {
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');

            if (checkedBoxes.length === 0) {
                const params = new URLSearchParams(window.location.search);
                window.location.href = '{{ route("users.export") }}?' + params.toString();
            } else {
                const userIds = Array.from(checkedBoxes).map(cb => cb.value);
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("users.export") }}';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                userIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'user_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }
        }
    </script>
@endsection
