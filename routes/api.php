<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/products/by-code', [ProductController::class, 'byCode']);

Route::get('/products/search', [ProductController::class, 'search']);

Route::get('/categories', [ProductController::class, 'categories']);
