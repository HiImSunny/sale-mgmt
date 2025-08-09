<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function invoice(Order $order)
    {
        $user = Auth::user();

        if (
            $user->role !== 'admin' &&
            $user->role !== 'seller' &&
            $order->user_id !== $user->id
        ) {
            abort(403);
        }

        $order->load(['items.productVariant.product', 'items.productVariant.attributeValues.attribute', 'user']);

        $data = [
            'order' => $order,
            'company' => [
                'name' => 'Cửa hàng Pacific Store',
                'address' => '168 Nguyễn Văn Cừ Nối Dài, An Bình, Ninh Kiều, Cần Thơ',
                'phone' => '0292 3798 668',
                'email' => 'info@pacific.store'
            ]
        ];

        $pdf = Pdf::loadView('pdf.invoice', $data)
            ->setPaper('a4', 'portrait')  // ← A4 portrait cho hóa đơn
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10,
                'margin_right' => 10
            ]);

        return $pdf->stream("hoa-don-{$order->code}.pdf");
    }
}
