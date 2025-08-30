<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TripController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth
Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('logout', 'logout')->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', function (Request $request) {
        return $request->user();
    });

    // Trip
    Route::apiResource('trip', TripController::class);

    // Payment & order
    Route::prefix('payment')->name('payment.')->controller(PaymentController::class)->group(function () {
        Route::get('pay/{order}', 'pay')->name('pay');
        Route::get('callback/{payment:transaction_id}', 'callback')->name('callback');
    });
});
