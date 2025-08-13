@extends('layouts.app')

@section('title', 'Bán hàng tại quầy')
@section('page-title', 'Bán hàng tại quầy')

@section('content')
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-7">
                <div class="pos-products">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h4 class="mb-0">
                                        <i class="fas fa-search me-2"></i>
                                        Tìm kiếm sản phẩm
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">

                            {{-- Search Bar --}}
                            <div class="search-section mb-3">
                                <div class="input-group">
                                    <input type="text" id="unified-search" class="form-control form-control-lg"
                                           placeholder="Tìm theo tên, SKU, mã vạch... hoặc dùng máy quét"
                                           autocomplete="off">
                                    <button type="button" class="btn btn-outline-success" onclick="toggleBarcodeScanner()" id="barcode-scanner-btn">
                                        <i class="fas fa-barcode" id="scanner-icon"></i>
                                        <span id="scanner-text">Máy quét</span>
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="performSearch(document.getElementById('unified-search').value)">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>

                                {{-- Barcode Scanner Status --}}
                                <div id="barcode-scanner-status" class="mt-2" style="display: none;">
                                    <div class="alert alert-info d-flex align-items-center">
                                        <i class="fas fa-barcode fa-2x me-3"></i>
                                        <div>
                                            <strong>Máy quét mã vạch đã sẵn sàng</strong>
                                            <br><small>Quét mã vạch bất kỳ để thêm sản phẩm vào giỏ hàng</small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" onclick="toggleBarcodeScanner()">
                                            Tắt
                                        </button>
                                    </div>
                                </div>

                                <div id="search-results" class="search-results mt-2"></div>
                            </div>

                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Mẹo sử dụng:</strong>
                                Nhập tên sản phẩm hoặc mã để tìm nhanh •
                                Bấm <i class="fas fa-barcode"></i> để bật máy quét USB •
                                Click vào kết quả để thêm vào giỏ
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cart & Checkout --}}
            <div class="col-lg-5">
                <div class="pos-cart">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Giỏ hàng <span class="badge bg-secondary ms-2" id="cart-count">0</span></h6>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearCart()">
                                <i class="bi bi-trash"></i> Xóa tất cả
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="cart-items" class="cart-items">
                                <div class="empty-cart text-center py-4">
                                    <i class="bi bi-cart text-muted fs-1"></i>
                                    <p class="text-muted">Chưa có sản phẩm nào</p>
                                </div>
                            </div>

                            <div id="cart-summary" class="cart-summary" style="display: none;">
                                <hr>
                                <div class="summary-row">
                                    <span>Tạm tính:</span>
                                    <span id="subtotal">0đ</span>
                                </div>
                                <div class="summary-row">
                                    <span>Giảm giá:</span>
                                    <span id="discount">0đ</span>
                                </div>
                                <div class="summary-row total">
                                    <strong>Tổng cộng:</strong>
                                    <strong id="total" class="text-accent">0đ</strong>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="payment-method mb-3">
                                <label class="form-label">Phương thức thanh toán:</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="payment_method" id="cash" value="cash_at_counter" checked>
                                    <label class="btn btn-outline-primary" for="cash">
                                        <i class="fas fa-money-bill me-1"></i>Tiền mặt
                                    </label>

                                    <input type="radio" class="btn-check" name="payment_method" id="vnpay" value="vnpay">
                                    <label class="btn btn-outline-primary" for="vnpay">
                                        <i class="fas fa-credit-card me-1"></i>VNPAY
                                    </label>
                                </div>
                            </div>

                            <button id="confirm-payment" type="button" class="btn btn-success btn-lg w-100 mb-2" disabled>
                                <i class="bi bi-credit-card me-2"></i>Xác nhận thanh toán
                            </button>

                            <button id="complete-order" type="button" class="btn btn-primary btn-lg w-100" style="display: none;">
                                <i class="bi bi-printer me-2"></i>Hoàn tất & In hóa đơn
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}">
@endpush


@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <script src="{{ asset('js/pos.js') }}"></script>
@endpush
