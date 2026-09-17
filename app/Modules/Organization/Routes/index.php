<?php

use App\Modules\Organization\Controllers\OrganizationController;
use App\Modules\Organization\Controllers\OrganizationMemberController;
use Illuminate\Support\Facades\Route;

Route::prefix('organizations')->controller(OrganizationController::class)->middleware(['auth:api', 'active.user'])->group(function () {
    Route::get('/{organization}/dashboard', 'dashboard');
});

Route::prefix('organization')->controller(OrganizationMemberController::class)
    ->middleware(['auth:api', 'active.user', 'organization.context', 'organization.member'])->group(function () {
        Route::get('/members', 'index')->middleware('permission:organization.member.view');
        Route::post('/members', 'store')->middleware('permission:organization.member.invite');
        Route::delete('/members/{member}', 'destroy')->middleware('permission:organization.member.remove');
        Route::get('/roles', 'roles')->middleware('permission:organization.member.view');
        Route::put('/members/{member}/roles', 'updateRoles')->middleware('permission:organization.member.update');
    });
