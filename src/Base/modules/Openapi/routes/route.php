<?php

use Illuminate\Support\Facades\Route;
use Xditn\Base\modules\Openapi\Http\Controllers\OpenapiRequestLogController;
use Xditn\Base\modules\Openapi\Http\Controllers\UsersController;

Route::prefix('openapi')->group(function () {

    Route::apiResource('users', UsersController::class);
    Route::put('user/{id}/regenerate', [UsersController::class, 'regenerate']);
    Route::post('user/charge', [UsersController::class, 'charge']);

    Route::apiResource('openapi/request/log', OpenapiRequestLogController::class);
    //next
});
