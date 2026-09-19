<?php

use App\Modules\Identity\Controllers\IdentityController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->controller(IdentityController::class)->group(function () {
    Route::post('/login', 'login');
    Route::middleware(['auth:api', 'active.user'])->group(function () {
        Route::post('/logout', 'logout');
        Route::post('/refresh', 'refresh');
        Route::get('/me', 'me');
    });
});

Route::prefix('me')->controller(IdentityController::class)->middleware(['auth:api', 'active.user'])->group(function () {
    Route::get('/organizations', 'organizations');
    Route::get('/projects', 'projects')->middleware(['organization.context', 'organization.member']);
    Route::get('/context', 'context')->middleware(['organization.context', 'organization.member', 'project.context', 'project.member']);
});

// Backward-compatible endpoint; new clients should use /me/organizations.
Route::get('auth/organizations', [IdentityController::class, 'organizations'])->middleware(['auth:api', 'active.user']);
