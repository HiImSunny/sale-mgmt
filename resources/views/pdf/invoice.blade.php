<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hóa đơn #{{ $order->code }}</title>
    <style>
        /* Print-optimized styles */
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .invoice-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #8b4513;
            margin-bottom: 5px;
        }
        
        .invoice-title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 20px 0;
        }
        
        .invoice-info {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .info-left, .info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .items-table th {
            background-color: #f8f1e8;
            font-weight: bold;
            text-align: center;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .total-section {
            float: right;
            width: 300px;
            margin-top: 20px;
        }
        
        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        
        .total-label, .total-value {
            display: table-cell;
            padding: 5px;
        }
        
        .total-label {
            text-align: left;
            width: 60%;
        }
        
        .total-value {
            text-align: right;
            width: 40%;
            font-weight: bold;
        }
        
        .grand-total {
            border-top: 2px solid #333;
            font-size: 14px;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 11px;
            color: #666;
            clear: both;
        }
        
        .print-timestamp {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <!-- Print timestamp -->
    <div class="print-timestamp">
        In lúc: {{ now()->format('d/m/Y H:i:s') }}
    </div>

    <!-- Header -->
    <div class="invoice-header">
        <div class="company-name">{{ $company['name'] }}</div>
        <div>{{ $company['address'] }}</div>
        <div>ĐT: {{ $company['phone'] }} | Email: {{ $company['email'] }}</div>
    </div>

    <div class="invoice-title text-center">HÓA ĐƠN BÁN HÀNG</div>

    <!-- Invoice Info -->
    <div class="invoice-info">
        <div class="info-left">
            <strong>Số hóa đơn:</strong> {{ $order->code }}<br>
            <strong>Ngày tạo:</strong> {{ $order->created_at->format('d/m/Y H:i') }}<br>
            <strong>Nhân viên:</strong> {{ $order->user->name ?? 'POS System' }}
        </div>
        <div class="info-right">
            @if($order->user && $order->user->role === 'customer')
                <strong>Khách hàng:</strong> {{ $order->user->name }}<br>
                <strong>Email:</strong> {{ $order->user->email }}<br>
            @else
                <strong>Khách hàng:</strong> Khách vãng lai<br>
            @endif
            <strong>Phương thức TT:</strong> 
            @switch($order->payment_method)
                @case('cash_at_counter') Tiền mặt @break
                @case('vnpay') VNPAY @break
                @case('cod') COD @break
                @default {{ $order->payment_method }}
            @endswitch
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">STT</th>
                <th width="40%">Sản phẩm</th>
                <th width="15%">SKU</th>
                <th width="12%">Đơn giá</th>
                <th width="8%">SL</th>
                <th width="20%">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    {{ $item->name_snapshot }}
                    @if($item->attributes_snapshot)
                        <br><small style="color: #666;">
                            @foreach($item->attributes_snapshot as $attr)
                                {{ $attr['attribute_name'] }}: {{ $attr['value'] }}@if(!$loop->last), @endif
                            @endforeach
                        </small>
                    @endif
                </td>
                <td>{{ $item->sku_snapshot }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 0, ',', '.') }}₫</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->line_total, 0, ',', '.') }}₫</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="total-section">
        <div class="total-row">
            <div class="total-label">Tạm tính:</div>
            <div class="total-value">{{ number_format($order->subtotal, 0, ',', '.') }}₫</div>
        </div>
        
        @if($order->discount_total > 0)
        <div class="total-row">
            <div class="total-label">Giảm giá:</div>
            <div class="total-value">-{{ number_format($order->discount_total, 0, ',', '.') }}₫</div>
        </div>
        @endif
        
        @if($order->shipping_fee > 0)
        <div class="total-row">
            <div class="total-label">Phí vận chuyển:</div>
            <div class="total-value">{{ number_format($order->shipping_fee, 0, ',', '.') }}₫</div>
        </div>
        @endif
        
        <div class="total-row grand-total">
            <div class="total-label">TỔNG CỘNG:</div>
            <div class="total-value">{{ number_format($order->grand_total, 0, ',', '.') }}₫</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Cảm ơn quý khách đã mua hàng!</p>
        <p>Hóa đơn được in tự động từ hệ thống POS</p>
    </div>
</body>
</html>