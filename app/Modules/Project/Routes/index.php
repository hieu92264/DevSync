<?php

use App\Modules\Project\Controllers\ProjectMemberController;
use Illuminate\Support\Facades\Route;

Route::prefix('project')->controller(ProjectMemberController::class)
    ->middleware(['auth:api', 'active.user', 'organization.context', 'organization.member', 'project.context', 'project.member'])->group(function () {
        Route::get('/members', 'index')->middleware('permission:project.member.view');
        Route::post('/members', 'store')->middleware('permission:project.member.manage');
        Route::delete('/members/{member}', 'destroy')->middleware('permission:project.member.manage');
        Route::get('/roles', 'roles')->middleware('permission:project.member.view');
        Route::put('/members/{member}/roles', 'updateRoles')->middleware('permission:project.member.manage');
    });
