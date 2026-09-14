<?php

use App\Modules\Organization\Controllers\OrganizationController;
use Illuminate\Support\Facades\Route;

Route::prefix('organizations')->controller(OrganizationController::class)->group(function () {
    Route::get('/{organization}/dashboard', 'dashboard');
});
