<?php

use App\Modules\Identity\Controllers\IdentityController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->controller(IdentityController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/logout', 'logout');
    Route::post('/refresh', 'refresh');
    Route::get('/me', 'me');
    Route::get('/organizations', 'organizations');
});
