<?php

use Illuminate\Support\Facades\Route;
use Xditn\Base\modules\Member\Http\Controllers\MemberGroupsController;
use Xditn\Base\modules\Member\Http\Controllers\MembersController;

Route::prefix('member')->group(function () {

    Route::apiResource('members', MembersController::class);
    Route::put('members/enable/{id}', [MembersController::class, 'enable']);

    Route::apiResource('member/groups', MemberGroupsController::class);
    Route::put('member/groups/enable/{id}', [MemberGroupsController::class, 'enable']);
    //next
});
