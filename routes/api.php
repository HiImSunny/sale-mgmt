<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DiscountController;

Route::get('/products/by-code', [ProductController::class, 'byCode']);

Route::get('/products/search', [ProductController::class, 'search']);

Route::get('/categories', [ProductController::class, 'categories']);

Route::get('/customers/search', [CustomerController::class, 'search']);

Route::get('/customers/by-phone', [CustomerController::class, 'findByPhone']);
