<?php

use Illuminate\Support\Facades\Route;
use Modules\Buses\Http\Controllers\BusesController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('buses', BusesController::class)->names('buses');
});
