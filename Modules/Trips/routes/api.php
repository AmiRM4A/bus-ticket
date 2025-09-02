<?php

use Illuminate\Support\Facades\Route;
use Modules\Trips\Http\Controllers\TripsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('trips', TripsController::class)->names('trips');
});
