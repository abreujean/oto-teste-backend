<?php

use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::prefix('v1')->middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('user-profile', [AuthController::class, 'userProfile']);
    
    Route::apiResource('products', ProductController::class);
    Route::apiResource('orders', OrderController::class);
    Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus']);
    
    Route::apiResource('users', UserController::class);
    Route::get('users/{id}/orders', [UserController::class, 'orders']);
});
