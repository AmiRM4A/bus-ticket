<?php

use App\Http\Controllers\TripController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('trip')->controller(TripController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{trip_id}', 'show');
    Route::post('/{trip_id}', 'store');
    Route::delete('/{trip_id}', 'destroy');
});