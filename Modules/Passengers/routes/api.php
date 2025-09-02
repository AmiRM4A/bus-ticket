<?php

use Illuminate\Support\Facades\Route;
use Modules\Passengers\Http\Controllers\PassengersController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('passengers', PassengersController::class)->names('passengers');
});
