<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Customer;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth();
        $lastMonthStart = $lastMonth->copy()->startOfMonth();
        $lastMonthEnd = $lastMonth->copy()->endOfMonth();

        $analytics = $this->getAnalyticsData($today, $yesterday, $thisMonth, $lastMonthStart, $lastMonthEnd);

        return view('dashboard', compact('analytics')); //  Sửa view path
    }

    private function getAnalyticsData($today, $yesterday, $thisMonth, $lastMonthStart, $lastMonthEnd)
    {
        // Basic metrics
        $todaySales = Order::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('grand_total');

        $yesterdaySales = Order::whereDate('created_at', $yesterday)
            ->where('status', 'completed')
            ->sum('grand_total');

        $monthlyRevenue = Order::where('created_at', '>=', $thisMonth)
            ->where('status', 'completed')
            ->sum('grand_total');

        $lastMonthRevenue = Order::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->where('status', 'completed')
            ->sum('grand_total');

        // Calculate growth rates
        $salesGrowth = $yesterdaySales > 0 ?
            round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 2) : 0;

        $monthlyGrowth = $lastMonthRevenue > 0 ?
            round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 2) : 0;

        // Orders data
        $todayOrders = Order::whereDate('created_at', $today)->count();
        $completedOrders = Order::whereDate('created_at', $today)
            ->where('status', 'completed')->count();
        $pendingOrders = Order::where('status', 'pending')->count();

        // Customer data
        $newCustomersToday = Customer::whereDate('created_at', $today)->count();
        $totalCustomers = Customer::count();

        // Employee data
        $activeUsers = User::whereIn('role', ['admin', 'seller'])
            ->where('updated_at', '>=', Carbon::now()->subHour())
            ->count();
        $totalEmployees = User::whereIn('role', ['admin', 'seller'])->count();

        // Stock data
        $lowStockCount = ProductVariant::where('stock', '<=', 10)->count();
        $lowStockProducts = ProductVariant::with('product')
            ->where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get();

        // Failed payments
        $failedPayments = Order::whereDate('created_at', $today)
            ->where('payment_status', 'failed')
            ->count();

        // Recent orders
        $recentOrders = Order::with(['customer', 'user'])
            ->latest()
            ->limit(10)
            ->get();

        // Top products
        $topProducts = $this->getTopProducts();

        // Customer tiers
        $customerTiers = $this->getCustomerTiers();

        // Chart data
        $salesChartData = $this->getSalesChartData('7days');
        $categoryData = $this->getCategoryRevenueData();
        $paymentMethodData = $this->getPaymentMethodData();

        // Recent activities
        $recentActivities = $this->getRecentActivities();

        return [
            'todaySales' => $todaySales,
            'salesGrowth' => $salesGrowth,
            'todayOrders' => $todayOrders,
            'completedOrders' => $completedOrders,
            'lowStockCount' => $lowStockCount,
            'newCustomersToday' => $newCustomersToday,
            'totalCustomers' => $totalCustomers,
            'activeUsers' => $activeUsers,
            'totalEmployees' => $totalEmployees,
            'monthlyRevenue' => $monthlyRevenue,
            'monthlyGrowth' => $monthlyGrowth,
            'pendingOrders' => $pendingOrders,
            'failedPayments' => $failedPayments,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
            'lowStockProducts' => $lowStockProducts,
            'customerTiers' => $customerTiers,
            'salesChartLabels' => $salesChartData['labels'],
            'salesChartData' => $salesChartData['data'],
            'categoryLabels' => $categoryData['labels'],
            'categoryData' => $categoryData['data'],
            'paymentLabels' => $paymentMethodData['labels'],
            'paymentData' => $paymentMethodData['data'],
            'recentActivities' => $recentActivities,
        ];
    }

    private function getTopProducts()
    {
        //  SỬA: Bỏ p.thumbnail vì không tồn tại trong schema
        return DB::table('order_items as oi')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->join('product_variants as pv', 'oi.product_variant_id', '=', 'pv.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->where('o.status', 'completed')
            ->where('o.created_at', '>=', Carbon::now()->subDays(30))
            ->select(
                'p.id',
                'p.name',
                'pv.stock',
                'pv.sku', //  Thêm SKU để hiển thị
                DB::raw('SUM(oi.quantity) as total_sold'),
                DB::raw('SUM(oi.line_total) as revenue')
            )
            ->groupBy('p.id', 'p.name', 'pv.stock', 'pv.sku')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get()
            ->map(function($product) {
                //  Set thumbnail null hoặc default image
                $product->thumbnail = null;
                return $product;
            });
    }

    private function getCustomerTiers()
    {
        $tiers = Customer::select('customer_tier', DB::raw('COUNT(*) as count'))
            ->groupBy('customer_tier')
            ->get();

        $totalCustomers = Customer::count();
        $tierClasses = [
            'bronze' => 'secondary',
            'silver' => 'info',
            'gold' => 'warning',
            'platinum' => 'primary'
        ];

        return $tiers->map(function ($tier) use ($totalCustomers, $tierClasses) {
            return [
                'tier' => $tier->customer_tier,
                'count' => $tier->count,
                'percentage' => $totalCustomers > 0 ? round(($tier->count / $totalCustomers) * 100, 1) : 0,
                'class' => $tierClasses[$tier->customer_tier] ?? 'secondary'
            ];
        });
    }

    private function getSalesChartData($period = '7days')
    {
        switch ($period) {
            case '30days':
                $startDate = Carbon::now()->subDays(30);
                $format = 'j/n';
                break;
            case '12months':
                $startDate = Carbon::now()->subMonths(12);
                $format = 'n/Y';
                break;
            default: // 7days
                $startDate = Carbon::now()->subDays(7);
                $format = 'j/n';
                break;
        }

        // ✅ SỬA: Group BY expression phù hợp với strict mode
        if ($period === '12months') {
            $salesData = Order::where('created_at', '>=', $startDate)
                ->where('status', 'completed')
                ->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(grand_total) as total_sales'),
                    DB::raw('DATE(MIN(created_at)) as date') // ✅ Aggregate function
                )
                ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc') // ✅ Order by grouped columns
                ->get();
        } else {
            $salesData = Order::where('created_at', '>=', $startDate)
                ->where('status', 'completed')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(grand_total) as total_sales')
                )
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy(DB::raw('DATE(created_at)'), 'asc') // ✅ Order by grouped expression
                ->get();
        }

        $labels = [];
        $data = [];

        if ($period === '12months') {
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $labels[] = $date->format('n/Y');

                $salesForMonth = $salesData->first(function ($item) use ($date) {
                    return $item->year == $date->year && $item->month == $date->month;
                });

                $data[] = $salesForMonth ? $salesForMonth->total_sales : 0;
            }
        } else {
            $days = $period === '30days' ? 30 : 7;
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $labels[] = $date->format($format);

                $salesForDay = $salesData->first(function ($item) use ($date) {
                    return Carbon::parse($item->date)->isSameDay($date);
                });

                $data[] = $salesForDay ? $salesForDay->total_sales : 0;
            }
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getCategoryRevenueData()
    {
        //  Kiểm tra xem có categories không trước khi query
        $categoryCount = Category::count();
        if ($categoryCount === 0) {
            return [
                'labels' => ['Chưa có danh mục'],
                'data' => [0]
            ];
        }

        $categoryRevenue = DB::table('order_items as oi')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->join('product_variants as pv', 'oi.product_variant_id', '=', 'pv.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->join('product_categories as pc', 'p.id', '=', 'pc.product_id')
            ->join('categories as c', 'pc.category_id', '=', 'c.id')
            ->where('o.status', 'completed')
            ->where('o.created_at', '>=', Carbon::now()->subDays(30))
            ->select('c.name', DB::raw('SUM(oi.line_total) as revenue'))
            ->groupBy('c.id', 'c.name')
            ->orderBy('revenue', 'desc')
            ->limit(6)
            ->get();

        if ($categoryRevenue->isEmpty()) {
            return [
                'labels' => ['Chưa có dữ liệu'],
                'data' => [0]
            ];
        }

        return [
            'labels' => $categoryRevenue->pluck('name')->toArray(),
            'data' => $categoryRevenue->pluck('revenue')->toArray()
        ];
    }

    private function getPaymentMethodData()
    {
        // ✅ Lấy data trực tiếp từ Orders thay vì Payments
        $paymentMethods = Order::where('status', 'completed')
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->select('payment_method', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        if ($paymentMethods->isEmpty()) {
            return [
                'labels' => ['Chưa có dữ liệu'],
                'data' => [0]
            ];
        }

        // ✅ Updated labels theo schema orders
        $methodLabels = [
            'vnpay' => 'VNPay',
            'cash_at_counter' => 'Tiền mặt tại quầy',
            'cod' => 'COD'
        ];

        return [
            'labels' => $paymentMethods->map(function ($item) use ($methodLabels) {
                return $methodLabels[$item->payment_method] ?? ucfirst(str_replace('_', ' ', $item->payment_method));
            })->toArray(),
            'data' => $paymentMethods->pluck('count')->toArray()
        ];
    }


    private function getRecentActivities()
    {
        $activities = collect();

        // Recent orders
        $recentOrders = Order::with('user')
            ->latest()
            ->limit(5)
            ->get();

        foreach ($recentOrders as $order) {
            $activities->push([
                'type' => 'order_created',
                'description' => "Đơn hàng {$order->code} được tạo",
                'user' => $order->user ? $order->user->name : 'Hệ thống',
                'time' => $order->created_at->diffForHumans(),
                'created_at' => $order->created_at
            ]);
        }

        // Recent customers
        $recentCustomers = Customer::latest()
            ->limit(3)
            ->get();

        foreach ($recentCustomers as $customer) {
            $activities->push([
                'type' => 'customer_registered',
                'description' => "Khách hàng {$customer->name} đăng ký",
                'user' => 'Hệ thống',
                'time' => $customer->created_at->diffForHumans(),
                'created_at' => $customer->created_at
            ]);
        }

        // Recent products
        $recentProducts = Product::latest()
            ->limit(3)
            ->get();

        foreach ($recentProducts as $product) {
            $activities->push([
                'type' => 'product_added',
                'description' => "Sản phẩm {$product->name} được thêm",
                'user' => 'Admin',
                'time' => $product->created_at->diffForHumans(),
                'created_at' => $product->created_at
            ]);
        }

        return $activities->sortByDesc('created_at')->take(10)->values();
    }

    public function getSalesChart(Request $request)
    {
        $period = $request->get('period', '7days');
        $chartData = $this->getSalesChartData($period);

        return response()->json($chartData);
    }

    public function refreshDashboard()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $analytics = $this->getAnalyticsData($today, $yesterday, $thisMonth, $lastMonthStart, $lastMonthEnd);

        return response()->json([
            'success' => true,
            'analytics' => $analytics
        ]);
    }
}
