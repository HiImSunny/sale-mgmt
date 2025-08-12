<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Hóa Đơn Hoàn Hàng #{{ $return['return_code'] }}</title>
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
            padding: 20px; /* Thêm padding để thụt vào */
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

        .return-badge {
            background-color: #d32f2f;
            color: white;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            display: inline-block;
            text-transform: uppercase;
            border-radius: 4px; /* Thêm bo góc nhẹ */
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
        .status-pending { color: #f57f17; font-weight: bold; }
        .status-approved { color: #2e7d32; font-weight: bold; }
        .status-completed { color: #1976d2; font-weight: bold; }
        .status-rejected { color: #d32f2f; font-weight: bold; }

        /* Original Invoice Info */
        .original-invoice-info {
            background-color: #fff8e1;
            border-left: 4px solid #d4a574;
            padding: 16px;
            margin-bottom: 18px;
            width: 100%;
            border-radius: 0 6px 6px 0; /* Bo góc phải */
        }

        .original-invoice-info h3 {
            color: #5d4037;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .original-invoice-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .original-invoice-table td {
            border: none;
            padding: 2px 8px 2px 0;
            vertical-align: top;
            font-size: 11px;
            word-wrap: break-word;
        }

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
            border-radius: 6px; /* Bo góc */
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

            border-radius: 6px; /* Bo góc */
        }

        /* Table */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            table-layout: fixed;
            border-radius: 6px; /* Bo góc table */
            overflow: hidden; /* Để border-radius hiệu quả */
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
        .col-product { width: 36%; }
        .col-qty { width: 12%; }
        .col-price { width: 15%; }
        .col-total { width: 15%; }
        .col-condition { width: 16%; }

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
            width: 380px; /* Giảm một chút để cân đối với padding */
            margin: 0 auto;
        }

        .total-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border-radius: 6px; /* Bo góc */
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

        /* Reason Section */
        .reason-section {
            margin-top: 22px;
            margin-bottom: 22px;
            padding: 16px;
            border: 2px solid #ef5350;
            background-color: #ffebee;
            border-radius: 6px; /* Bo góc */
        }

        .reason-section h3 {
            color: #c62828;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .reason-section p {
            color: #6d1b7b;
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
            width: 33.33%;
            height: 90px;
            word-wrap: break-word;
            border-radius: 6px; /* Bo góc nhẹ */
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
            border-radius: 6px; /* Bo góc */
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
                </td>
                <td class="invoice-info">
                    <div class="return-badge">HÓA ĐƠN HOÀN HÀNG</div>
                    <div class="invoice-meta">
                        <p><strong>Mã HĐ Hoàn:</strong> {{ $return['return_code'] }}</p>
                        <p><strong>Ngày hoàn:</strong> {{ $return['return_date']->format('d/m/Y H:i') }}</p>
                        <p><strong>Trạng thái:</strong>
                            @switch($return['status'])
                                @case('pending')
                                    <span class="status-pending">Chờ xử lý</span>
                                    @break
                                @case('approved')
                                    <span class="status-approved">Đã duyệt</span>
                                    @break
                                @case('completed')
                                    <span class="status-completed">Hoàn tất</span>
                                    @break
                                @case('rejected')
                                    <span class="status-rejected">Từ chối</span>
                                    @break
                            @endswitch
                        </p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Thông tin hóa đơn gốc --}}
    <div class="original-invoice-info">
        <h3>Thông tin hóa đơn gốc</h3>
        <table class="original-invoice-table">
            <tr>
                <td style="width: 50%;">
                    <p><strong>Mã HĐ gốc:</strong> {{ $return['originalInvoice']['invoice_number'] ?? 'N/A' }}</p>
                    <p><strong>Ngày lập:</strong> {{ $return['originalInvoice']['created_at']->format('d/m/Y') ?? 'N/A' }}</p>
                </td>
                <td style="width: 50%;">
                    <p><strong>Khách hàng:</strong> {{ $return['originalInvoice']['customer_name'] ?? 'N/A' }}</p>
                    <p><strong>Tổng tiền gốc:</strong> {{ number_format($return['originalInvoice']['total_amount'] ?? 0, 0, ',', '.') }} VNĐ</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- Thông tin xử lý --}}
    <div class="info-section">
        <table class="info-table">
            <tr>
                <td>
                    <div class="info-box">
                        <h3>Thông tin khách hàng</h3>
                        <p><strong>Tên:</strong> {{ $return['originalInvoice']['customer_name'] ?? 'N/A' }}</p>
                        <p><strong>SĐT:</strong> {{ $return['originalInvoice']['customer_phone'] ?? 'N/A' }}</p>
                        <p><strong>Địa chỉ:</strong> {{ $return['originalInvoice']['customer_address'] ?? 'N/A' }}</p>
                    </div>
                </td>
                <td>
                    <div class="info-box" style="margin-right: 0;">
                        <h3>Người xử lý</h3>
                        <p><strong>Tên:</strong> {{ $return['processedBy']->name ?? 'N/A' }}</p>
                        <p><strong>Chức vụ:</strong> Nhân viên bán hàng</p>
                        <p><strong>Ngày xử lý:</strong> {{ $return['created_at']->format('d/m/Y H:i') }}</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Chi tiết sản phẩm hoàn --}}
    <div class="section-title">Chi tiết sản phẩm hoàn hàng</div>

    <table class="data-table">
        <thead>
        <tr>
            <th class="col-stt">STT</th>
            <th class="col-product">Tên sản phẩm</th>
            <th class="col-qty">SL hoàn</th>
            <th class="col-price">Đơn giá</th>
            <th class="col-total">Thành tiền</th>
            <th class="col-condition">Tình trạng</th>
        </tr>
        </thead>
        <tbody>
        @foreach($return['returnItems'] as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item['product']['name'] ?? 'N/A' }}</td>
                <td class="text-center">{{ $item['quantity_returned'] }}</td>
                <td class="text-right">{{ number_format($item['unit_price'], 0, ',', '.') }} VNĐ</td>
                <td class="text-right">{{ number_format($item['total_price'], 0, ',', '.') }} VNĐ</td>
                <td class="text-center">{{ $item['item_condition'] ?? 'Bình thường' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- Tổng cộng --}}
    <div class="total-wrapper">
        <div class="total-section">
            <table class="total-table">
                <tr>
                    <td class="total-label">Tổng tiền hàng hoàn:</td>
                    <td class="total-value">{{ number_format($return['total_return_amount'], 0, ',', '.') }} VNĐ</td>
                </tr>
                <tr>
                    <td class="total-label">Phí xử lý:</td>
                    <td class="total-value">0 VNĐ</td>
                </tr>
                <tr class="final-row">
                    <td>TỔNG TIỀN HOÀN:</td>
                    <td>{{ number_format($return['total_return_amount'], 0, ',', '.') }} VNĐ</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Lý do hoàn hàng --}}
    <div class="reason-section">
        <h3>Lý do hoàn hàng</h3>
        <p>{{ $return['reason'] }}</p>
    </div>

    {{-- Chữ ký --}}
    <div class="signatures">
        <table class="signature-table">
            <tr>
                <td>
                    <strong>Khách hàng</strong>
                    <div class="signature-line">
                        (Ký và ghi rõ họ tên)
                    </div>
                </td>
                <td>
                    <strong>Nhân viên xử lý</strong>
                    <div class="signature-line">
                        {{ $return['processedBy']->name ?? '' }}
                    </div>
                </td>
                <td>
                    <strong>Thủ kho</strong>
                    <div class="signature-line">
                        (Ký và ghi rõ họ tên)
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p><strong>Lưu ý quan trọng:</strong></p>
        <ul>
            <li>Khách hàng vui lòng kiểm tra kỹ thông tin trước khi ký xác nhận</li>
            <li>Hóa đơn hoàn hàng này có giá trị pháp lý</li>
            <li>Liên hệ {{ $company['phone'] }} để được hỗ trợ thêm</li>
        </ul>
        <div class="thanks">
            Cảm ơn quý khách đã sử dụng dịch vụ của chúng tôi!
        </div>
    </div>
</div>
</body>
</html>
