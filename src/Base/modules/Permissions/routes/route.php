<?php

use Illuminate\Support\Facades\Route;
use Xditn\Base\modules\Permissions\Http\Controllers\DepartmentsController;
use Xditn\Base\modules\Permissions\Http\Controllers\JobsController;
use Xditn\Base\modules\Permissions\Http\Controllers\PermissionsController;
use Xditn\Base\modules\Permissions\Http\Controllers\RolesController;
use Xditn\Base\modules\Permissions\Http\Controllers\DemosController;
use Xditn\Base\modules\Permissions\Http\Controllers\DemoController;

Route::prefix('permissions')->group(function () {
    Route::apiResource('roles', RolesController::class);

    Route::apiResource('jobs', JobsController::class);
    Route::put('jobs/enable/{id}', [JobsController::class, 'enable']);

    Route::apiResource('departments', DepartmentsController::class);
    Route::put('departments/enable/{id}', [DepartmentsController::class, 'enable']);

    Route::apiResource('permissions', PermissionsController::class);
    Route::put('permissions/enable/{id}', [PermissionsController::class, 'enable']);

    Route::get('menu/all', [PermissionsController::class, 'menu']);
});


