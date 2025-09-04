<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\UsersController;

Route::controller(UsersController::class)->prefix('v1/user')->name('user.')->group(function () {
    Route::post('register', 'register')->name('register');
    Route::post('login', 'login')->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', 'logout')->name('logout');
        Route::get('/', 'me')->name('me');
    });
});
