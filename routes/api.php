<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CustomerController;

Route::get('/products/by-code', [ProductController::class, 'byCode']);

Route::get('/products/search', [ProductController::class, 'search']);

Route::get('/categories', [ProductController::class, 'categories']);

Route::get('/api/customers/search', [CustomerController::class, 'search']);

Route::middleware(['auth'])->group(function () {
    Route::get('/api/test', function() {
        return response()->json(['user' => auth()->user()]);
    });
});
