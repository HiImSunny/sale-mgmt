<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProfileController;
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

    Route::resource('products', ProductController::class);

    Route::post('products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('products.bulk-delete');
    Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
    Route::post('products/export', [ProductController::class, 'export'])->name('products.export.selected'); // For selected items
    Route::delete('products/{product}/images/{image}', [ProductController::class, 'deleteImage'])->name('products.images.delete');

    Route::prefix('products/{product}')->group(function () {
        Route::get('variants', [ProductVariantController::class, 'index'])->name('product-variants.index');
        Route::get('variants/create', [ProductVariantController::class, 'create'])->name('product-variants.create');
        Route::post('variants', [ProductVariantController::class, 'store'])->name('product-variants.store');
    });

    Route::prefix('product-variants')->group(function () {
        Route::get('{variant}/edit', [ProductVariantController::class, 'edit'])->name('product-variants.edit');
        Route::put('{variant}', [ProductVariantController::class, 'update'])->name('product-variants.update');
        Route::delete('{variant}', [ProductVariantController::class, 'destroy'])->name('product-variants.destroy');
    });

    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::prefix('backup')->name('backup.')->group(function () {
        Route::get('/', [SettingsController::class, 'backupIndex'])->name('index');
        Route::post('create-full', [SettingsController::class, 'createFullBackup'])->name('createFull');
        Route::post('create-db', [SettingsController::class, 'createDatabaseBackup'])->name('createDB');
        Route::post('restore', [SettingsController::class, 'restoreBackup'])->name('restore');
        Route::get('download/{filename}', [SettingsController::class, 'downloadBackup'])->name('download');
        Route::delete('delete', [SettingsController::class, 'deleteBackup'])->name('delete');
    });
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

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/refund', [OrderController::class, 'createRefund'])->name('orders.create-refund');

    Route::get('/products/', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}/', [ProductController::class, 'show'])->name('products.show');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::get('/orders/{order}/refund-invoice', [OrderController::class, 'refundInvoice'])->name('orders.refund-invoice');
});

Route::get('/vnpay/return', [VNPayController::class, 'handleReturn'])->name('vnpay.return');

require __DIR__.'/auth.php';
