{{-- resources/views/vnpay/pos-result.blade.php --}}
    <!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả thanh toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/25f90b730d.js" crossorigin="anonymous"></script>
</head>
<body class="bg-light">
<div class="container-fluid d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-lg" style="max-width: 500px; width: 100%;">
        <div class="card-body text-center p-5">
            @if($success)
                <div class="text-success mb-4">
                    <i class="fas fa-check-circle fa-5x"></i>
                </div>
                <h3 class="text-success mb-3">Thanh toán thành công!</h3>
                <p class="text-muted mb-4">{{ $message }}</p>

                @if($order)
                    <div class="alert alert-success">
                        <strong>Mã đơn hàng:</strong> {{ $order->code }}<br>
                        <strong>Số tiền:</strong> {{ number_format($order->grand_total) }}đ
                    </div>
                @endif

                <button type="button" class="btn btn-success btn-lg" onclick="notifyParentAndClose('success')">
                    <i class="fas fa-check me-2"></i>Hoàn tất
                </button>
            @else
                <div class="text-danger mb-4">
                    <i class="fas fa-times-circle fa-5x"></i>
                </div>
                <h3 class="text-danger mb-3">Thanh toán thất bại!</h3>
                <p class="text-muted mb-4">{{ $message }}</p>

                <button type="button" class="btn btn-secondary btn-lg" onclick="notifyParentAndClose('failed')">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                </button>
            @endif
        </div>
    </div>
</div>

<script>
    function notifyParentAndClose(status) {
        if (window.opener) {
            // Gửi kết quả về parent window
            window.opener.postMessage({
                type: 'vnpay_payment_result',
                success: {{ $success ? 'true' : 'false' }},
                message: '{{ $message }}',
                @if($order)
                order: {
                    id: {{ $order->id }},
                    code: '{{ $order->code }}',
                    total: {{ $order->grand_total }}
                }
                @endif
            }, '*');
        }

        // Đóng popup sau 1 giây
        setTimeout(() => {
            window.close();
        }, 1000);
    }

    // Auto close after 10 seconds if not manually closed
    setTimeout(() => {
        notifyParentAndClose('timeout');
    }, 10000);
</script>
</body>
</html>
