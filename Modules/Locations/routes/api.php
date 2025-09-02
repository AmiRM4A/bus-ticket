<?php

use Illuminate\Support\Facades\Route;
use Modules\Locations\Http\Controllers\LocationsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('locations', LocationsController::class)->names('locations');
});
