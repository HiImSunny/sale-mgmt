<?php
// app/Services/VNPayQRService.php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class VNPayQRService
{
    private $vnp_TmnCode;
    private $vnp_HashSecret;
    private $vnp_Url;

    public function __construct()
    {
        $this->vnp_TmnCode = config('services.vnpay.tmn_code');
        $this->vnp_HashSecret = config('services.vnpay.hash_secret');
        $this->vnp_Url = config('services.vnpay.url');
    }

    public function generateVNPayLink($orderId, $amount, $description = 'Thanh toán đơn hàng POS')
    {
        $vnp_TxnRef = 'POS_' . $orderId . '_' . time();
        $vnp_Amount = $amount * 100;

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $this->vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => request()->ip(),
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => $description,
            "vnp_OrderType" => "other",
            "vnp_ReturnUrl" => config('services.vnpay.return_url'),
            "vnp_TxnRef" => $vnp_TxnRef,
        ];

        ksort($inputData);
        $query = "";
        $hashdata = "";
        $i = 0;

        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $this->vnp_Url . "?" . $query;
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->vnp_HashSecret);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;

        return [
            'url' => $vnp_Url,
            'txn_ref' => $vnp_TxnRef,
            'amount' => $vnp_Amount / 100
        ];
    }

    public function verifyPayment($request)
    {
        $vnp_SecureHash = $request->get('vnp_SecureHash');

        $inputData = array_filter($request->all(), function ($key) {
            return str_starts_with($key, "vnp_");
        }, ARRAY_FILTER_USE_KEY);

        unset($inputData['vnp_SecureHash']);
        ksort($inputData);

        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $this->vnp_HashSecret);

        return $secureHash === $vnp_SecureHash;
    }
}
