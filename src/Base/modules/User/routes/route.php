<?php

use Illuminate\Support\Facades\Route;
use Xditn\Modules\User\Http\Controllers\AuthController;
use Xditn\Modules\User\Http\Controllers\UserController;

// login route
Route::post('login', [AuthController::class, 'login'])->withoutMiddleware(config('xditn.route.middlewares'));
Route::post('logout', [AuthController::class, 'logout'])->withoutMiddleware(config('xditn.route.middlewares'));

// users route
Route::apiResource('users', UserController::class);
Route::put('users/enable/{id}', [UserController::class, 'enable']);
Route::match(['post', 'get'], 'user/online', [UserController::class, 'online']);
Route::get('user/login/log', [UserController::class, 'loginLog']);
Route::get('user/operate/log', [UserController::class, 'operateLog']);
Route::get('user/operate/log', [UserController::class, 'operateLog']);
Route::get('user/export', [UserController::class, 'export']);



