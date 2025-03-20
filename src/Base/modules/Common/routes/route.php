<?php

use Illuminate\Support\Facades\Route;
use Xditn\Base\modules\Common\Http\Controllers\AreaController;
use Xditn\Base\modules\Common\Http\Controllers\DemoController;
use Xditn\Base\modules\Common\Http\Controllers\EnableController;
use Xditn\Base\modules\Common\Http\Controllers\OptionController;
use Xditn\Base\modules\Common\Http\Controllers\ServerController;
use Xditn\Base\modules\Common\Http\Controllers\UploadController;
use Xditn\Base\modules\Permissions\Middlewares\PermissionGate;
use Xditn\Base\modules\User\Middlewares\OperatingMiddleware;
use Xditn\Middleware\AuthMiddleware;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::withoutMiddleware([
    PermissionGate::class,
    OperatingMiddleware::class,
])->get('options/{name}', [OptionController::class, 'index']);

// 配置开启
Route::withoutMiddleware([
    AuthMiddleware::class,
    PermissionGate::class,
    OperatingMiddleware::class,
])->prefix('enable')->group(function () {
    Route::get('login', [EnableController::class, 'login']);
});

Route::prefix('')->group(function () {
    // 上传
    Route::controller(UploadController::class)->group(function () {
        Route::post('upload/file', 'file')->withoutMiddleware(PermissionGate::class);
        Route::post('upload/image', 'image')->withoutMiddleware(PermissionGate::class);
        // get oss signature
        Route::get('upload/token', 'token');
    });
    // 地区
    Route::controller(AreaController::class)->group(function () {
        Route::get('areas', 'index');
    });
});

Route::get('server/info', [ServerController::class, 'info']);

// demo 路由，可删除
Route::prefix('demo')->group(function () {
   Route::get('exception', [DemoController::class, 'exception']);
   Route::get('dd', [DemoController::class, 'dd']);
});
