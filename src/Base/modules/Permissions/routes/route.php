<?php

use Illuminate\Support\Facades\Route;
use Xditn\Modules\Permissions\Http\Controllers\RolesController;
use Xditn\Modules\Permissions\Http\Controllers\JobsController;
use Xditn\Modules\Permissions\Http\Controllers\DepartmentsController;
use Xditn\Modules\Permissions\Http\Controllers\PermissionsController;

Route::prefix('permissions')->group(function () {
    Route::apiResource('roles', RolesController::class);

    Route::apiResource('jobs', JobsController::class);
    Route::put('jobs/enable/{id}', [JobsController::class, 'enable']);

    Route::apiResource('departments', DepartmentsController::class);
    Route::put('departments/enable/{id}', [DepartmentsController::class, 'enable']);

    Route::apiResource('permissions', PermissionsController::class);
    Route::put('permissions/enable/{id}', [PermissionsController::class, 'enable']);
    //next
});
