<?php

use Illuminate\Support\Facades\Route;
use Modules\Locations\Http\Controllers\LocationsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('locations', LocationsController::class)->names('locations');
});
