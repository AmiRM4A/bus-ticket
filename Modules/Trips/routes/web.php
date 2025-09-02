<?php

use Illuminate\Support\Facades\Route;
use Modules\Trips\Http\Controllers\TripsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('trips', TripsController::class)->names('trips');
});
