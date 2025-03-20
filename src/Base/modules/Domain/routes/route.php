<?php

use Illuminate\Support\Facades\Route;
use Xditn\Base\modules\Domain\Http\Controllers\DomainRecordsController;
use Xditn\Base\modules\Domain\Http\Controllers\DomainsController;

Route::prefix('domain')->group(function () {

    Route::apiResource('domains', DomainsController::class);
    Route::apiResource('domain/records', DomainRecordsController::class);
    //next
});
