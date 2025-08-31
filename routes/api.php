<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TripController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('logout', 'logout')->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('trip', TripController::class);

    Route::prefix('payment')->name('payment.')->controller(PaymentController::class)->group(function () {
        Route::get('pay/{order_id}', 'pay')->middleware('throttle:5,2')->name('pay');
        Route::get('callback/{payment:transaction_id}', 'callback')->name('callback');
    });

    Route::get('order/{order_id}', [OrderController::class, 'show']);
});
