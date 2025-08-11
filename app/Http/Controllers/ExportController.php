<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductVariant;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportController extends Controller
{
    public function orders()
    {
        $orders = Order::with(['user'])->latest()->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setTitle('Danh sách đơn hàng');

        // Headers
        $headers = [
            'A1' => 'Mã đơn',
            'B1' => 'Khách hàng',
            'C1' => 'Email',
            'D1' => 'Ngày tạo',
            'E1' => 'Trạng thái đơn',
            'F1' => 'Trạng thái thanh toán',
            'G1' => 'Phương thức thanh toán',
            'H1' => 'Tạm tính',
            'I1' => 'Giảm giá',
            'J1' => 'Phí ship',
            'K1' => 'Tổng cộng'
        ];

        // Set headers
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style headers
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        $sheet->getStyle('A1:K1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE6F3FF');
        $sheet->getStyle('A1:K1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Data
        $row = 2;
        foreach ($orders as $order) {
            $sheet->setCellValue('A' . $row, $order->code);
            $sheet->setCellValue('B' . $row, $order->user ? $order->user->name : 'Khách vãng lai');
            $sheet->setCellValue('C' . $row, $order->user ? $order->user->email : '');
            $sheet->setCellValue('D' . $row, $order->created_at->format('d/m/Y H:i'));
            $sheet->setCellValue('E' . $row, $this->getStatusText($order->status));
            $sheet->setCellValue('F' . $row, $this->getPaymentStatusText($order->payment_status));
            $sheet->setCellValue('G' . $row, $this->getPaymentMethodText($order->payment_method));
            $sheet->setCellValue('H' . $row, $order->subtotal);
            $sheet->setCellValue('I' . $row, $order->discount_total);
            $sheet->setCellValue('J' . $row, $order->shipping_fee);
            $sheet->setCellValue('K' . $row, $order->grand_total);
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Format currency columns
        $lastRow = $row - 1;
        $sheet->getStyle('H2:K' . $lastRow)->getNumberFormat()
            ->setFormatCode('#,##0 "₫"');

        $writer = new Xlsx($spreadsheet);

        $filename = 'danh-sach-don-hang-' . date('Y-m-d') . '.xlsx';

        $response = response()->stream(function() use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheettml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);

        return $response;
    }

    public function products()
    {
        $variants = ProductVariant::with(['product.categories', 'attributeValues.attribute'])->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setTitle('Danh sách sản phẩm');

        // Headers
        $headers = [
            'A1' => 'SKU',
            'B1' => 'EAN13',
            'C1' => 'UPC',
            'D1' => 'Tên sản phẩm',
            'E1' => 'Thuộc tính',
            'F1' => 'Giá gốc',
            'G1' => 'Giá khuyến mãi',
            'H1' => 'Tồn kho',
            'I1' => 'Danh mục',
            'J1' => 'Trạng thái'
        ];

        // Set headers
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style headers
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('A1:J1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF0F8E8');
        $sheet->getStyle('A1:J1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Data
        $row = 2;
        foreach ($variants as $variant) {
            // Get attributes
            $attributes = $variant->attributeValues->map(function($attrValue) {
                return $attrValue->attribute->name . ': ' . $attrValue->value;
            })->implode(', ');

            // Get categories
            $categories = $variant->product->categories->pluck('name')->implode(', ');

            $sheet->setCellValue('A' . $row, $variant->sku);
            $sheet->setCellValue('B' . $row, $variant->ean13 ?: '');
            $sheet->setCellValue('C' . $row, $variant->upc ?: '');
            $sheet->setCellValue('D' . $row, $variant->product->name);
            $sheet->setCellValue('E' . $row, $attributes);
            $sheet->setCellValue('F' . $row, $variant->price);
            $sheet->setCellValue('G' . $row, $variant->sale_price ?: 0);
            $sheet->setCellValue('H' . $row, $variant->stock);
            $sheet->setCellValue('I' . $row, $categories);
            $sheet->setCellValue('J' . $row, $variant->status == 1 ? 'Hoạt động' : 'Tạm dừng');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Format price columns
        $lastRow = $row - 1;
        $sheet->getStyle('F2:G' . $lastRow)->getNumberFormat()
            ->setFormatCode('#,##0 "₫"');

        // Format stock column (center alignment)
        $sheet->getStyle('H2:H' . $lastRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $writer = new Xlsx($spreadsheet);

        $filename = 'danh-sach-san-pham-' . date('Y-m-d') . '.xlsx';

        $response = response()->stream(function() use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheettml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);

        return $response;
    }

    private function getStatusText($status)
    {
        $statuses = [
            'pending' => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn thành',
            'canceled' => 'Đã hủy'
        ];

        return $statuses[$status] ?? $status;
    }

    private function getPaymentStatusText($status)
    {
        $statuses = [
            'unpaid' => 'Chưa thanh toán',
            'paid' => 'Đã thanh toán',
            'failed' => 'Thanh toán thất bại'
        ];

        return $statuses[$status] ?? $status;
    }

    private function getPaymentMethodText($method)
    {
        $methods = [
            'cash_at_counter' => 'Tiền mặt',
            'vnpay' => 'VNPAY',
            'cod' => 'COD'
        ];

        return $methods[$method] ?? $method;
    }
}
