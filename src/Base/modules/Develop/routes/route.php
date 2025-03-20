<?php

use Illuminate\Support\Facades\Route;
use Xditn\Base\modules\Develop\Http\Controllers\GenerateController;
use Xditn\Base\modules\Develop\Http\Controllers\ModuleController;
use Xditn\Base\modules\Develop\Http\Controllers\SchemaController;

Route::apiResource('module', ModuleController::class);
Route::post('module/install', [ModuleController::class, 'install']);
Route::post('module/upload', [ModuleController::class, 'upload']);

Route::put('module/enable/{name}', [ModuleController::class, 'enable']);

Route::post('generate', [GenerateController::class, 'index']);

Route::apiResource('schema', SchemaController::class)->only(['index', 'show', 'store', 'destroy']);
Route::match(['get', 'put'], 'schema/files/{id}', [SchemaController::class, 'files']);
