<?php

use Illuminate\Support\Facades\Route;
use Modules\Trips\Http\Controllers\TripsController;

Route::apiResource('v1/trips', TripsController::class)->except('destroy')->middleware('auth:sanctum');
