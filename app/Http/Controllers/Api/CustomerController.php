<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function search(Request $request)
    {
        $search = $request->get('q');

        $customers = Customer::where('name', 'like', "%{$search}%")
            ->orWhere('phone', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->limit(10)
            ->get(['id', 'name', 'phone', 'email', 'customer_tier', 'is_vip']);

        return response()->json($customers);
    }

    public function findByPhone(Request $request) {
        $query = Customer::where('phone', $request->get('phone'));
        if ($request->has('name')) $query->where('name', 'like', '%' . $request->get('name') . '%');
        $customer = $query->first();
        if ($customer) {
            return response()->json(['success' => true, 'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'points' => $customer->points
            ]]);
        }
        return response()->json(['success' => false]);
    }
}
