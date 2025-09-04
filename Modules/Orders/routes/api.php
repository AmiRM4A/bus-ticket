<?php

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\OrdersController;

Route::middleware('auth:sanctum')->prefix('v1/orders')->name('orders.')->controller(OrdersController::class)->group(function () {
    Route::get('{order_id}', 'show')->name('show');
    Route::delete('{order_id}', 'destroy')->name('destroy');
    Route::get('checkout/{order_id}', 'checkout')->name('checkout');
});
