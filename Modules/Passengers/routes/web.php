<?php

use Illuminate\Support\Facades\Route;
use Modules\Passengers\Http\Controllers\PassengersController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('passengers', PassengersController::class)->names('passengers');
});
