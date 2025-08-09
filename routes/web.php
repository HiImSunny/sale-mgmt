<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Seller\POSController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:seller,admin'])->prefix('seller')->group(function () {
    Route::get('/pos', [POSController::class, 'index'])->name('seller.pos');
    Route::post('/pos/create-order', [POSController::class, 'createOrder'])->name('seller.pos.create-order');
    Route::post('/pos/confirm-payment/{order}', [POSController::class, 'confirmPayment'])->name('seller.pos.confirm-payment');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/export/orders', [ExportController::class, 'orders'])->name('admin.export.orders');
    Route::get('/export/products', [ExportController::class, 'products'])->name('admin.export.products');
    
    Route::get('/dashboard', function() {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
});

require __DIR__.'/auth.php';
