<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discount;

class DiscountController extends Controller
{
    public function search()
    {
        try {
            $discounts = Discount::where('status', 1)
                ->where(function($query) {
                    $query->whereNull('start_at')
                        ->orWhere('start_at', '<=', now());
                })
                ->where(function($query) {
                    $query->whereNull('end_at')
                        ->orWhere('end_at', '>=', now());
                })
                ->get();

            return response()->json([
                'success' => true,
                'data' => $discounts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading discounts'
            ], 500);
        }
    }
}
