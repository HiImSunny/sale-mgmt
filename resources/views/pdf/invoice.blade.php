
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Hóa Đơn Bán Hàng #{{ $invoice['invoice_number'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 12mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #3c2e26;
            width: 100%;
        }

        .container {
            width: 95%;
            padding: 20px;
            min-height: 100vh;
            background: white;
        }

        /* Header */
        .header {
            border-bottom: 3px solid #8b6f47;
            padding-bottom: 18px;
            margin-bottom: 22px;
            width: 100%;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .header-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .company-info {
            width: 55%;
            padding-right: 15px;
        }

        .company-info h1 {
            color: #5d4037;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 8px;
            word-wrap: break-word;
        }

        .company-info p {
            color: #6d4c41;
            margin-bottom: 3px;
            font-size: 11px;
            word-wrap: break-word;
        }

        .invoice-info {
            width: 45%;
            text-align: right;
            padding-left: 15px;
        }

        .invoice-badge {
            background-color: #1976d2;
            color: white;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            display: inline-block;
            text-transform: uppercase;
            border-radius: 4px;
        }

        .invoice-meta p {
            margin-bottom: 4px;
            font-size: 11px;
            color: #5d4037;
            word-wrap: break-word;
        }

        .invoice-meta strong {
            color: #3e2723;
        }

        /* Status Colors */
        .status-draft { color: #757575; font-weight: bold; }
        .status-pending { color: #f57f17; font-weight: bold; }
        .status-paid { color: #2e7d32; font-weight: bold; }
        .status-overdue { color: #d32f2f; font-weight: bold; }
        .status-cancelled { color: #d32f2f; font-weight: bold; }

        /* Info Sections */
        .info-section {
            margin-bottom: 22px;
            width: 100%;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .info-table td {
            border: none;
            padding: 0;
            vertical-align: top;
            width: 50%;
        }

        .info-box {
            border: 1px solid #efebe9;
            padding: 14px;
            background-color: #fafafa;
            margin-right: 12px;
            height: 110px;
            border-radius: 6px;
        }

        .info-box h3 {
            color: #5d4037;
            margin-bottom: 8px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #d4a574;
            padding-bottom: 4px;
        }

        .info-box p {
            margin-bottom: 4px;
            color: #4e342e;
            font-size: 11px;
            word-wrap: break-word;
        }

        .info-box strong {
            color: #3e2723;
            font-weight: bold;
        }

        /* Section Title */
        .section-title {
            background-color: #8b6f47;
            color: white;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 16px;
            text-align: center;
            text-transform: uppercase;
            border-radius: 6px;
        }

        /* Table */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            table-layout: fixed;
            border-radius: 6px;
            overflow: hidden;
        }

        table.data-table th {
            background-color: #6d4c41;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 10px 4px;
            font-size: 11px;
            text-transform: uppercase;
            border: 1px solid #5d4037;
            word-wrap: break-word;
        }

        table.data-table td {
            border: 1px solid #efebe9;
            padding: 8px 4px;
            text-align: left;
            font-size: 11px;
            word-wrap: break-word;
            overflow: hidden;
        }

        table.data-table tr:nth-child(even) td {
            background-color: #fafafa;
        }

        /* Column widths */
        .col-stt { width: 6%; }
        .col-product { width: 32%; }
        .col-qty { width: 10%; }
        .col-unit { width: 8%; }
        .col-price { width: 15%; }
        .col-discount { width: 12%; }
        .col-total { width: 17%; }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        /* Total Section */
        .total-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-top: 16px;
            margin-bottom: 18px;
        }

        .total-section {
            width: 380px;
            margin: 0 auto;
        }

        .total-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border-radius: 6px;
            overflow: hidden;
        }

        .total-table td {
            padding: 8px 12px;
            border: 1px solid #efebe9;
            font-size: 12px;
            word-wrap: break-word;
        }

        .total-table .total-label {
            color: #4e342e;
            font-weight: normal;
            background-color: white;
            width: 65%;
        }

        .total-table .total-value {
            text-align: right;
            font-weight: normal;
            background-color: white;
            width: 35%;
        }

        .total-table .final-row td {
            background-color: #6d4c41;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        /* Payment Info Section */
        .payment-info {
            margin-top: 22px;
            margin-bottom: 22px;
            padding: 16px;
            border: 2px solid #4caf50;
            background-color: #f1f8e9;
            border-radius: 6px;
        }

        .payment-info h3 {
            color: #2e7d32;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .payment-info p {
            color: #1b5e20;
            font-weight: normal;
            line-height: 1.4;
            font-size: 11px;
            word-wrap: break-word;
            margin-bottom: 4px;
        }

        /* Notes Section */
        .notes-section {
            margin-top: 22px;
            margin-bottom: 22px;
            padding: 16px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            border-radius: 6px;
        }

        .notes-section h3 {
            color: #5d4037;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .notes-section p {
            color: #6d4c41;
            font-weight: normal;
            line-height: 1.4;
            font-size: 11px;
            word-wrap: break-word;
        }

        /* Signatures */
        .signatures {
            margin-top: 35px;
            padding-top: 18px;
            border-top: 1px solid #efebe9;
            width: 100%;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .signature-table td {
            border: 1px dashed #d4a574;
            text-align: center;
            padding: 16px 8px;
            vertical-align: top;
            width: 50%;
            height: 90px;
            word-wrap: break-word;
            border-radius: 6px;
        }

        .signature-table strong {
            color: #5d4037;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            display: block;
            margin-bottom: 8px;
        }

        .signature-line {
            border-top: 1px solid #8b6f47;
            margin-top: 30px;
            padding-top: 4px;
            font-size: 9px;
            color: #6d4c41;
            font-style: italic;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 16px;
            border-top: 1px solid #efebe9;
            font-size: 11px;
            color: #6d4c41;
        }

        .footer strong {
            color: #5d4037;
            font-size: 12px;
        }

        .footer ul {
            margin-left: 18px;
            margin-top: 8px;
        }

        .footer li {
            margin-bottom: 4px;
            line-height: 1.3;
            word-wrap: break-word;
        }

        .thanks {
            text-align: center;
            margin-top: 16px;
            padding: 12px;
            background-color: #fff8e1;
            font-style: italic;
            color: #5d4037;
            font-weight: normal;
            font-size: 11px;
            border-radius: 6px;
        }

        /* Due Date Alert */
        .due-date-alert {
            background-color: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 0 6px 6px 0;
        }

        .due-date-alert p {
            color: #e65100;
            font-weight: bold;
            margin: 0;
            font-size: 12px;
        }
    </style>
</head>
<body>
<div class="container">
    {{-- Header --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="company-info">
                    <h1>{{ $company['name'] }}</h1>
                    <p>{{ $company['address'] }}</p>
                    <p>SĐT: {{ $company['phone'] }}</p>
                    <p>Email: {{ $company['email'] }}</p>
                    @if(isset($company['tax_code']))
                        <p>Mã số thuế: {{ $company['tax_code'] }}</p>
                    @endif
                </td>
                <td class="invoice-info">
                    <div class="invoice-badge">HÓA ĐƠN BÁN HÀNG</div>
                    <div class="invoice-meta">
                        <p><strong>Số HĐ:</strong> {{ $invoice['invoice_number'] }}</p>
                        <p><strong>Ngày lập:</strong> {{ $invoice['invoice_date']->format('d/m/Y H:i') }}</p>
                        <p><strong>Ngày đáo hạn:</strong> {{ $invoice['due_date']->format('d/m/Y') }}</p>
                        <p><strong>Trạng thái:</strong>
                            @switch($invoice['status'])
                                @case('pending')
                                    <span class="status-pending">Chờ xử lý</span>
                                    @break
                                @case('completed')
                                    <span class="status-paid">Hoàn thành</span>
                                    @break
                                @case('cancelled')
                                    <span class="status-cancelled">Đã hủy</span>
                                    @break
                            @endswitch
                        </p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Due Date Alert --}}
    @if($invoice['status'] == 'overdue' || ($invoice['status'] == 'pending' && $invoice['due_date']->diffInDays(now()) <= 3))
        <div class="due-date-alert">
            <p>
                @if($invoice['status'] == 'overdue')
                    ⚠️ HÓA ĐƠN ĐÃ QUÁ HẠN THANH TOÁN
                @else
                    ⏰ HÓA ĐƠN SẮP ĐẾN HẠN THANH TOÁN ({{ $invoice['due_date']->format('d/m/Y') }})
                @endif
            </p>
        </div>
    @endif

    {{-- Thông tin khách hàng và người bán --}}
    <div class="info-section">
        <table class="info-table">
            <tr>
                <td>
                    <div class="info-box">
                        <h3>Thông tin khách hàng</h3>
                        <p><strong>Tên:</strong> {{ $invoice['customer']['name'] ?? 'N/A' }}</p>
                        <p><strong>SĐT:</strong> {{ $invoice['customer']['phone'] ?? 'N/A' }}</p>
                        <p><strong>Địa chỉ:</strong> {{ $invoice['customer']['address'] ?? 'N/A' }}</p>
                    </div>
                </td>
                <td>
                    <div class="info-box" style="margin-right: 0;">
                        <h3>Người lập hóa đơn</h3>
                        <p><strong>Tên:</strong> {{ $invoice['createdBy']->name ?? 'N/A' }}</p>
                        <p><strong>Chức vụ:</strong> {{ $invoice['createdBy']->position ?? 'Nhân viên bán hàng' }}</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Chi tiết sản phẩm --}}
    <div class="section-title">Chi tiết sản phẩm</div>

    <table class="data-table">
        <thead>
        <tr>
            <th class="col-stt">STT</th>
            <th class="col-product">Tên sản phẩm</th>
            <th class="col-qty">SL</th>
            <th class="col-price">Đơn giá</th>
            <th class="col-discount">Giảm giá</th>
            <th class="col-total">Thành tiền</th>
        </tr>
        </thead>
        <tbody>
        @foreach($invoice['invoiceItems'] as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    {{ $item['product']['name'] ?? 'N/A' }}
                    @if($item['product']['sku'])
                        <br><small style="color: #666;">SKU: {{ $item['product']['sku'] }}</small>
                    @endif
                </td>
                <td class="text-center">{{ $item['quantity'] }}</td>
                <td class="text-right">{{ number_format($item['unit_price'], 0, ',', '.') }} VNĐ</td>
                <td class="text-center">
                    @if($item['discount_percent'] > 0)
                        {{ $item['discount_percent'] }}%
                    @elseif($item['discount_amount'] > 0)
                        {{ number_format($item['discount_amount'], 0, ',', '.') }} VNĐ
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">{{ number_format($item['total_price'], 0, ',', '.') }} VNĐ</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- Tổng cộng --}}
    <div class="total-wrapper">
        <div class="total-section">
            <table class="total-table">
                <tr>
                    <td class="total-label">Tổng tiền hàng:</td>
                    <td class="total-value">{{ number_format($invoice['subtotal'], 0, ',', '.') }} VNĐ</td>
                </tr>
                <tr>
                    <td class="total-label">Tổng giảm giá:</td>
                    <td class="total-value">{{ number_format($invoice['total_discount'], 0, ',', '.') }} VNĐ</td>
                </tr>
                <tr class="final-row">
                    <td>TỔNG CỘNG:</td>
                    <td>{{ number_format($invoice['total_amount'], 0, ',', '.') }} VNĐ</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Thông tin thanh toán --}}
    <div class="payment-info">
        <h3>Thông tin thanh toán</h3>
        <p><strong>Phương thức:</strong> {{ $invoice['payment_method'] ?? 'Tiền mặt' }}</p>
    </div>

    {{-- Ghi chú --}}
    @if($invoice['notes'])
        <div class="notes-section">
            <h3>Ghi chú</h3>
            <p>{{ $invoice['notes'] }}</p>
        </div>
    @endif

    {{-- Chữ ký --}}
    <div class="signatures">
        <table class="signature-table">
            <tr>
                <td>
                    <strong>Người mua hàng</strong>
                    <div class="signature-line">
                        (Ký và ghi rõ họ tên)
                    </div>
                </td>
                <td>
                    <strong>Người bán hàng</strong>
                    <div class="signature-line">
                        {{ $invoice['createdBy']->name ?? '' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p><strong>Điều khoản và điều kiện:</strong></p>
        <ul>
            <li>Hóa đơn này có giá trị pháp lý</li>
            <li>Mọi thắc mắc xin liên hệ: {{ $company['phone'] }}</li>
        </ul>
        <div class="thanks">
            Cảm ơn quý khách đã tin tưởng và sử dụng dịch vụ của chúng tôi!
        </div>
    </div>
</div>
</body>
</html>
