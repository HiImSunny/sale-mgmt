<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VNPayController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'dashboard']);
    Route::get('/dashboard/sales-chart', [DashboardController::class, 'getSalesChart']);
    Route::get('/dashboard/refresh', [DashboardController::class, 'refreshDashboard']);

    Route::get('/export/orders', [ExportController::class, 'orders'])->name('admin.export.orders');
    Route::get('/export/products', [ExportController::class, 'products'])->name('admin.export.products');
});

Route::middleware(['auth', 'role:seller,admin'])->group(function () {
    Route::get('/pos', [POSController::class, 'index'])->name('pos');

    Route::post('/pos/create-order', [POSController::class, 'createOrder'])->name('pos.create-order');
    Route::get('/pos/check-payment-status/{order}', [POSController::class, 'checkPaymentStatus']);
    Route::post('/pos/confirm-payment/{order}', [POSController::class, 'confirmPayment'])->name('pos.confirm-payment');

    Route::get('/pos/search-orders', [POSController::class, 'searchOrders'])->name('pos.search-orders');
    Route::get('/pos/order-details/{order}', [POSController::class, 'getOrderDetails'])->name('pos.order-details');
    Route::post('/pos/create-refund', [POSController::class, 'createRefund'])->name('pos.create-refund');

    Route::resource('customers', CustomerController::class);
    Route::get('/customers/{customer}/orders', [CustomerController::class, 'orders'])->name('customers.orders');
    Route::post('/customers/{customer}/update-tier', [CustomerController::class, 'updateTier'])->name('customers.update-tier');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/refund', [OrderController::class, 'createRefund'])->name('orders.create-refund');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
});

Route::get('/vnpay/return', [VNPayController::class, 'handleReturn'])->name('vnpay.return');

require __DIR__.'/auth.php';
