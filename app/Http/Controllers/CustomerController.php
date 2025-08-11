<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        // ✅ Search functionality
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // ✅ Filter by tier
        if ($tier = $request->get('tier')) {
            $query->where('customer_tier', $tier);
        }

        // ✅ Filter by VIP status
        if ($request->has('vip')) {
            $query->where('is_vip', $request->get('vip') === '1');
        }

        // ✅ Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');

        $allowedSorts = ['name', 'phone', 'total_spent', 'total_orders', 'customer_tier', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $customers = $query->paginate(20)->withQueryString();

        // ✅ Statistics
        $stats = [
            'total' => Customer::count(),
            'bronze' => Customer::where('customer_tier', 'bronze')->count(),
            'silver' => Customer::where('customer_tier', 'silver')->count(),
            'gold' => Customer::where('customer_tier', 'gold')->count(),
            'platinum' => Customer::where('customer_tier', 'platinum')->count(),
            'vip' => Customer::where('is_vip', true)->count(),
            'new_this_month' => Customer::whereDate('created_at', '>=', Carbon::now()->startOfMonth())->count(),
        ];

        return view('customer.index', compact('customers', 'stats'));
    }

    public function create()
    {
        return view('customer.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'email' => 'nullable|email|unique:customers,email',
            'birthday' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
        ]);

        $customer = Customer::create($validated);

        return redirect()->route('customers.show', $customer)
            ->with('swal_success', 'Khách hàng đã được tạo thành công!');
    }

    public function show(Customer $customer)
    {
        // ✅ Load orders với pagination
        $orders = Order::where('customer_id', $customer->id)
            ->with(['orderItems.product', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // ✅ Customer statistics
        $stats = [
            'total_orders' => $customer->orders()->count(),
            'completed_orders' => $customer->orders()->where('status', 'completed')->count(),
            'pending_orders' => $customer->orders()->where('status', 'pending')->count(),
            'canceled_orders' => $customer->orders()->where('status', 'canceled')->count(),
            'total_spent' => $customer->orders()->where('status', 'completed')->sum('grand_total'),
            'avg_order_value' => $customer->orders()->where('status', 'completed')->avg('grand_total') ?? 0,
            'last_order' => $customer->orders()->latest()->first(),
            'first_order' => $customer->orders()->oldest()->first(),
        ];

        // ✅ Monthly spending chart (last 12 months)
        $monthlySpending = $this->getMonthlySpending($customer);

        // ✅ Top purchased products
        $topProducts = $this->getTopProducts($customer);

        return view('customer.show', compact('customer', 'orders', 'stats', 'monthlySpending', 'topProducts'));
    }

    public function edit(Customer $customer)
    {
        return view('customer.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone,' . $customer->id,
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'birthday' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
            'customer_tier' => 'required|in:bronze,silver,gold,platinum',
            'is_vip' => 'boolean',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)
            ->with('swal_success', 'Thông tin khách hàng đã được cập nhật!');
    }

    public function destroy(Customer $customer)
    {
        // ✅ Soft check - không xóa nếu có đơn hàng
        if ($customer->orders()->exists()) {
            return back()->with('swal_error', 'Không thể xóa khách hàng có lịch sử mua hàng!');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('swal_success', 'Khách hàng đã được xóa!');
    }


    // ✅ Customer orders history (for modal/ajax)
    public function orders(Customer $customer)
    {
        $orders = Order::where('customer_id', $customer->id)
            ->with(['orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('customer.orders', compact('customer', 'orders'));
    }

    // ✅ Update customer tier manually
    public function updateTier(Customer $customer, Request $request)
    {
        $validated = $request->validate([
            'tier' => 'required|in:bronze,silver,gold,platinum',
            'is_vip' => 'boolean'
        ]);

        $customer->update([
            'customer_tier' => $validated['tier'],
            'is_vip' => $validated['is_vip'] ?? false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cấp độ khách hàng đã được cập nhật!'
        ]);
    }

    private function getMonthlySpending(Customer $customer)
    {
        $months = [];
        $spending = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');

            $monthSpending = $customer->orders()
                ->where('status', 'completed')
                ->whereBetween('created_at', [
                    $month->copy()->startOfMonth(),
                    $month->copy()->endOfMonth()
                ])
                ->sum('grand_total');

            $spending[] = $monthSpending;
        }

        return [
            'labels' => $months,
            'data' => $spending
        ];
    }

    private function getTopProducts(Customer $customer)
    {
        return DB::table('order_items as oi')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->where('o.customer_id', $customer->id)
            ->where('o.status', 'completed')
            ->select(
                'p.name',
                'oi.sku_snapshot',
                DB::raw('SUM(oi.quantity) as total_quantity'),
                DB::raw('SUM(oi.line_total) as total_spent'),
                DB::raw('COUNT(DISTINCT o.id) as order_count')
            )
            ->groupBy('p.id', 'p.name', 'oi.sku_snapshot')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();
    }
}
