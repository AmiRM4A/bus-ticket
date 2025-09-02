<?php

use Illuminate\Support\Facades\Route;
use Modules\Drivers\Http\Controllers\DriversController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('drivers', DriversController::class)->names('drivers');
});
