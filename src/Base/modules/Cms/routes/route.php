<?php

use Illuminate\Support\Facades\Route;
use Xditn\Base\modules\Cms\Http\Controllers\CategoryController;
use Xditn\Base\modules\Cms\Http\Controllers\PostController;
use Xditn\Base\modules\Cms\Http\Controllers\ResourceController;
use Xditn\Base\modules\Cms\Http\Controllers\SettingController;
use Xditn\Base\modules\Cms\Http\Controllers\TagController;

Route::prefix('cms')->group(function () {

    Route::apiResource('category', CategoryController::class)->names('cms_category');

    Route::apiResource('post', PostController::class);
    Route::put('post/enable/{id}', [PostController::class, 'enable']);

    Route::apiResource('tag', TagController::class);

    Route::post('setting', [SettingController::class, 'store']);
    Route::get('setting/{key?}', [SettingController::class, 'index']);

    Route::apiResource('resource', ResourceController::class);
    Route::put('resource/enable/{id}', [ResourceController::class, 'enable']);

    //next
});
