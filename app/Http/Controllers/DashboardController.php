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

        return view('dashboard', compact('analytics'));
    }

    private function getAnalyticsData($today, $yesterday, $thisMonth, $lastMonthStart, $lastMonthEnd)
    {
        // ✅ Fixed: Trừ refund khỏi doanh thu
        $todaySales = Order::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->selectRaw("
                SUM(CASE
                    WHEN type = 'sale' THEN grand_total
                    WHEN type = 'refund' THEN -grand_total
                    ELSE 0
                END) as revenue
            ")
            ->value('revenue') ?? 0;

        $yesterdaySales = Order::whereDate('created_at', $yesterday)
            ->where('status', 'completed')
            ->selectRaw("
                SUM(CASE
                    WHEN type = 'sale' THEN grand_total
                    WHEN type = 'refund' THEN -grand_total
                    ELSE 0
                END) as revenue
            ")
            ->value('revenue') ?? 0;

        $monthlyRevenue = Order::where('created_at', '>=', $thisMonth)
            ->where('status', 'completed')
            ->selectRaw("
                SUM(CASE
                    WHEN type = 'sale' THEN grand_total
                    WHEN type = 'refund' THEN -grand_total
                    ELSE 0
                END) as revenue
            ")
            ->value('revenue') ?? 0;

        $lastMonthRevenue = Order::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->where('status', 'completed')
            ->selectRaw("
                SUM(CASE
                    WHEN type = 'sale' THEN grand_total
                    WHEN type = 'refund' THEN -grand_total
                    ELSE 0
                END) as revenue
            ")
            ->value('revenue') ?? 0;

        // Calculate growth rates
        $salesGrowth = $yesterdaySales > 0 ?
            round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 2) : 0;

        $monthlyGrowth = $lastMonthRevenue > 0 ?
            round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 2) : 0;

        // ✅ Fixed: Orders data - chỉ đếm orders bán hàng
        $todayOrders = Order::whereDate('created_at', $today)
            ->where('type', 'sale')
            ->count();

        $completedOrders = Order::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->where('type', 'sale')
            ->count();

        $pendingOrders = Order::where('status', 'pending')
            ->where('type', 'sale')
            ->count();

        // Customer data
        $newCustomersToday = Customer::whereDate('created_at', $today)->count();
        $totalCustomers = Customer::count();

        // Employee data
        $activeUsers = User::whereIn('role', ['admin', 'seller'])
            ->where('updated_at', '>=', Carbon::now()->subHour())
            ->count();
        $totalEmployees = User::whereIn('role', ['admin', 'seller'])->count();

        // ✅ FIXED: Stock data - sử dụng stock_quantity thay vì stock
        $lowStockCount = $this->getLowStockCount();
        $lowStockProducts = $this->getLowStockProducts();

        // ✅ Fixed: Failed payments - chỉ đếm orders bán hàng
        $failedPayments = Order::whereDate('created_at', $today)
            ->where('payment_status', 'failed')
            ->where('type', 'sale')
            ->count();

        // Recent orders - keep as is since you already filter out refunds
        $recentOrders = Order::whereNot('type', 'refund')->with(['customer', 'user'])
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

    // ✅ NEW: Method riêng để tính low stock với logic mới
    private function getLowStockCount()
    {
        // Đếm products đơn giản có stock thấp
        $simpleProductsLowStock = Product::where('has_variants', false)
            ->where('stock_quantity', '<=', 10)
            ->count();

        // Đếm variants có stock thấp
        $variantsLowStock = ProductVariant::where('stock_quantity', '<=', 10)
            ->count();

        return $simpleProductsLowStock + $variantsLowStock;
    }

    // ✅ NEW: Method riêng để lấy low stock products với logic mới
    private function getLowStockProducts()
    {
        $lowStockItems = collect();

        // Lấy simple products có stock thấp
        $simpleProducts = Product::where('has_variants', false)
            ->where('stock_quantity', '<=', 10)
            ->orderBy('stock_quantity', 'asc')
            ->limit(5)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock_quantity' => $product->stock_quantity,
                    'type' => 'simple_product'
                ];
            });

        // Lấy variants có stock thấp
        $variants = ProductVariant::with('product')
            ->where('stock_quantity', '<=', 10)
            ->orderBy('stock_quantity', 'asc')
            ->limit(5)
            ->get()
            ->map(function($variant) {
                return [
                    'id' => $variant->id,
                    'name' => $variant->product->name,
                    'sku' => $variant->sku,
                    'stock_quantity' => $variant->stock_quantity,
                    'type' => 'variant'
                ];
            });

        return $lowStockItems->merge($simpleProducts)
            ->merge($variants)
            ->sortBy('stock_quantity')
            ->take(10)
            ->values();
    }

    // ✅ FIXED: Top products với stock_quantity
    private function getTopProducts()
    {
        return DB::table('order_items as oi')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->join('product_variants as pv', 'oi.product_variant_id', '=', 'pv.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->where('o.status', 'completed')
            ->where('o.type', 'sale') // ✅ Fixed: Chỉ lấy orders bán hàng
            ->where('o.created_at', '>=', Carbon::now()->subDays(30))
            ->select(
                'p.id',
                'p.name',
                'pv.stock_quantity', // ✅ FIXED: stock -> stock_quantity
                'pv.sku',
                DB::raw('SUM(oi.quantity) as total_sold'),
                DB::raw('SUM(oi.line_total) as revenue')
            )
            ->groupBy('p.id', 'p.name', 'pv.stock_quantity', 'pv.sku')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get()
            ->map(function($product) {
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
            'platinum' => 'platinum',
            'gold' => 'gold',
            'silver' => 'silver',
            'bronze' => 'bronze',
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

        if ($period === '12months') {
            // ✅ Fixed: Trừ refund trong chart data
            $salesData = Order::where('created_at', '>=', $startDate)
                ->where('status', 'completed')
                ->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw("
                        SUM(CASE
                            WHEN type = 'sale' THEN grand_total
                            WHEN type = 'refund' THEN -grand_total
                            ELSE 0
                        END) as total_sales
                    "),
                    DB::raw('DATE(MIN(created_at)) as date')
                )
                ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
        } else {
            // ✅ Fixed: Trừ refund trong chart data
            $salesData = Order::where('created_at', '>=', $startDate)
                ->where('status', 'completed')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw("
                        SUM(CASE
                            WHEN type = 'sale' THEN grand_total
                            WHEN type = 'refund' THEN -grand_total
                            ELSE 0
                        END) as total_sales
                    ")
                )
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy(DB::raw('DATE(created_at)'), 'asc')
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

    // ✅ FIXED: Category revenue với relationship mới
    private function getCategoryRevenueData()
    {
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
            ->where('o.type', 'sale')
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
        // ✅ Fixed: Chỉ lấy orders bán hàng
        $paymentMethods = Order::where('status', 'completed')
            ->where('payment_status', 'paid')
            ->where('type', 'sale') // ✅ Chỉ lấy orders bán hàng
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

        // ✅ Fixed: Chỉ lấy orders bán hàng trong recent activities
        $recentOrders = Order::with('user')
            ->where('type', 'sale') // ✅ Chỉ lấy orders bán hàng
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

        try {
            $chartData = $this->getSalesChartData($period);

            return response()->json([
                'success' => true,
                'data' => $chartData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
            ], 500);
        }
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
