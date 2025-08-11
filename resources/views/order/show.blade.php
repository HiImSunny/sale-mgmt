@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng - ' . $order->code)
@section('page-title', 'Chi tiết đơn hàng')

@section('content')
    <div class="container-fluid">
        {{-- Order Header --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-receipt me-2"></i>Đơn hàng {{ $order->code }}
                            </h5>
                            <div class="btn-group">
                                @if($order->status !== 'canceled')
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                                        <i class="fas fa-edit me-2"></i>Cập nhật trạng thái
                                    </button>
                                @endif
                                @if($order->type === 'sale' && $order->status === 'completed')
                                    <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#refundModal">
                                        <i class="fas fa-undo me-2"></i>Hoàn trả
                                    </button>
                                @endif
                                @if($order->status === 'completed')
                                    <a href="{{ route('orders.invoice', $order) }}" class="btn btn-success" target="_blank">
                                        <i class="fas fa-print me-2"></i>In hóa đơn
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-info-circle me-2"></i>Thông tin đơn hàng</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Mã đơn hàng:</td>
                                        <td class="fw-bold">{{ $order->code }}</td>
                                    </tr>
                                    <tr>
                                        <td>Loại đơn:</td>
                                        <td>
                                        <span class="badge bg-{{ $order->type === 'sale' ? 'success' : 'warning' }}">
                                            {{ $order->type === 'sale' ? 'Bán hàng' : 'Hoàn trả' }}
                                        </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Trạng thái:</td>
                                        <td>
                                        <span class="order-status-badge status-{{ $order->status }}">
                                            @switch($order->status)
                                                @case('pending') Chờ xử lý @break
                                                @case('completed') Hoàn thành @break
                                                @case('canceled') Đã hủy @break
                                            @endswitch
                                        </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Thanh toán:</td>
                                        <td>
                                        <span class="order-status-badge payment-{{ $order->payment_status }}">
                                            @switch($order->payment_status)
                                                @case('unpaid') Chưa thanh toán @break
                                                @case('paid') Đã thanh toán @break
                                                @case('failed') Thất bại @break
                                            @endswitch
                                        </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Phương thức TT:</td>
                                        <td>
                                            @switch($order->payment_method)
                                                @case('vnpay') VNPay @break
                                                @case('cod') COD @break
                                                @case('cash_at_counter') Tiền mặt tại quầy @break
                                            @endswitch
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <h6><i class="fas fa-user me-2"></i>Thông tin khách hàng</h6>
                                @if($order->customer)
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Tên:</td>
                                            <td class="fw-bold">{{ $order->customer->name }}</td>
                                        </tr>
                                        <tr>
                                            <td>SĐT:</td>
                                            <td>{{ $order->customer->phone }}</td>
                                        </tr>
                                        <tr>
                                            <td>Email:</td>
                                            <td>{{ $order->customer->email ?? 'Không có' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Tier:</td>
                                            <td>
                                            <span class="badge bg-{{ $order->customer->customer_tier === 'platinum' ? 'primary' : ($order->customer->customer_tier === 'gold' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($order->customer->customer_tier) }}
                                            </span>
                                            </td>
                                        </tr>
                                    </table>
                                @else
                                    <p class="text-muted">Khách lẻ</p>
                                @endif
                            </div>
                        </div>

                        @if($order->notes)
                            <div class="mt-3">
                                <h6><i class="fas fa-sticky-note me-2"></i>Ghi chú</h6>
                                <p class="text-muted">{{ $order->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Order Items --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-box me-2"></i>Sản phẩm ({{ $orderStats['items_count'] }} món - {{ $orderStats['total_quantity'] }} cái)
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>SKU</th>
                                    <th>Đơn giá</th>
                                    <th>Số lượng</th>
                                    <th>Thành tiền</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $item->name_snapshot }}</div>
                                            @php
                                                $attrs = is_string($item->attributes_snapshot)
                                                    ? json_decode($item->attributes_snapshot, true)
                                                    : $item->attributes_snapshot;
                                            @endphp

                                            @if(!empty($attrs))
                                                <small class="text-muted">
                                                    {{ collect($attrs)->pluck('value')->join(', ') }}
                                                </small>
                                            @endif

                                        </td>
                                        <td>{{ $item->sku_snapshot }}</td>
                                        <td>{{ number_format($item->unit_price) }}đ</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td class="fw-bold">{{ number_format($item->line_total) }}đ</td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th colspan="4">Tạm tính:</th>
                                    <th>{{ number_format($order->subtotal) }}đ</th>
                                </tr>
                                @if($order->discount_total > 0)
                                    <tr>
                                        <th colspan="4">Giảm giá:</th>
                                        <th class="text-danger">-{{ number_format($order->discount_total) }}đ</th>
                                    </tr>
                                @endif
                                <tr class="table-warning">
                                    <th colspan="4">Tổng cộng:</th>
                                    <th class="text-success">{{ number_format($order->grand_total) }}đ</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Refund Orders (if any) --}}
        @if($order->refundOrders->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-undo me-2"></i>Đơn hoàn trả
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                    <tr>
                                        <th>Mã đơn hoàn trả</th>
                                        <th>Lý do</th>
                                        <th>Số tiền</th>
                                        <th>Ngày tạo</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($order->refundOrders as $refund)
                                        <tr>
                                            <td>
                                                <a href="{{ route('orders.show', $refund) }}" class="fw-bold">
                                                    {{ $refund->code }}
                                                </a>
                                            </td>
                                            <td>
                                                <div>{{ ucfirst(str_replace('_', ' ', $refund->refund_reason)) }}</div>
                                                @if($refund->refund_reason_detail)
                                                    <small class="text-muted">{{ $refund->refund_reason_detail }}</small>
                                                @endif
                                            </td>
                                            <td class="text-danger fw-bold">-{{ number_format($refund->grand_total) }}đ</td>
                                            <td>{{ $refund->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Update Status Modal --}}
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('orders.update-status', $order) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Cập nhật trạng thái đơn hàng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Trạng thái đơn hàng</label>
                            <select name="status" class="form-select" required>
                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                                <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                <option value="canceled" {{ $order->status === 'canceled' ? 'selected' : '' }}>Đã hủy</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Trạng thái thanh toán</label>
                            <select name="payment_status" class="form-select" required>
                                <option value="unpaid" {{ $order->payment_status === 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
                                <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                                <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Thất bại</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="notes" class="form-control" rows="3">{{ $order->notes }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Refund Modal --}}
    @if($order->type === 'sale' && $order->status === 'completed')
        <div class="modal fade" id="refundModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="{{ route('orders.create-refund', $order) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Tạo đơn hoàn trả</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Lý do hoàn trả</label>
                                <select name="refund_reason" class="form-select" required>
                                    <option value="">Chọn lý do</option>
                                    <option value="customer_request">Yêu cầu khách hàng</option>
                                    <option value="damaged_product">Sản phẩm bị hỏng</option>
                                    <option value="wrong_product">Sai sản phẩm</option>
                                    <option value="quality_issue">Vấn đề chất lượng</option>
                                    <option value="other">Khác</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Chi tiết lý do</label>
                                <textarea name="refund_reason_detail" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sản phẩm hoàn trả</label>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                        <tr>
                                            <th>Chọn</th>
                                            <th>Sản phẩm</th>
                                            <th>Số lượng có thể hoàn</th>
                                            <th>Số lượng hoàn trả</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($order->items as $item)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="refund_items[{{ $loop->index }}][selected]" value="1" onchange="toggleRefundItem({{ $loop->index }})">
                                                    <input type="hidden" name="refund_items[{{ $loop->index }}][order_item_id]" value="{{ $item->id }}">
                                                </td>
                                                <td>
                                                    <div class="fw-bold">{{ $item->name_snapshot }}</div>
                                                    <small class="text-muted">{{ $item->sku_snapshot }}</small>
                                                </td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>
                                                    <input type="number"
                                                           name="refund_items[{{ $loop->index }}][quantity]"
                                                           class="form-control form-control-sm"
                                                           min="1"
                                                           max="{{ $item->quantity }}"
                                                           value="{{ $item->quantity }}"
                                                           disabled
                                                           id="refund_qty_{{ $loop->index }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-warning">Tạo hoàn trả</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        function toggleRefundItem(index) {
            const checkbox = document.querySelector(`input[name="refund_items[${index}][selected]"]`);
            const qtyInput = document.getElementById(`refund_qty_${index}`);

            qtyInput.disabled = !checkbox.checked;
            if (!checkbox.checked) {
                qtyInput.value = '';
            }
        }
    </script>
@endpush
