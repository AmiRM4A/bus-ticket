<?php

use Illuminate\Support\Facades\Route;
use Modules\Payments\Http\Controllers\PaymentsController;

Route::prefix('v1/payments')->name('payments.')->group(function () {
    Route::get('callback/{payment:transaction_id}', [PaymentsController::class, 'callback'])->name('callback');
});
