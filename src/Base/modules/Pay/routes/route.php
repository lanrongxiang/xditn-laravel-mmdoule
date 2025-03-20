<?php

use Illuminate\Support\Facades\Route;
use Xditn\Base\modules\Pay\Http\Controllers\ConfigController;

Route::prefix('pay')->group(function () {
    // 获取配置
    Route::get('config/{driver}', [ConfigController::class, 'show']);
    // 保存配置
    Route::post('config', [ConfigController::class, 'store']);
});
