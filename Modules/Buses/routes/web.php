<?php

use Illuminate\Support\Facades\Route;
use Modules\Buses\Http\Controllers\BusesController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('buses', BusesController::class)->names('buses');
});
