<?php

use Illuminate\Support\Facades\Route;
use Xditn\Base\modules\System\Http\Controllers\AsyncTaskController;
use Xditn\Base\modules\System\Http\Controllers\ConnectorLogController;
use Xditn\Base\modules\System\Http\Controllers\CronTasksController;
use Xditn\Base\modules\System\Http\Controllers\CronTasksLogController;
use Xditn\Base\modules\System\Http\Controllers\DictionaryController;
use Xditn\Base\modules\System\Http\Controllers\DictionaryValuesController;
use Xditn\Base\modules\System\Http\Controllers\DomainConfigController;
use Xditn\Base\modules\System\Http\Controllers\PersonalAccessTokensController;
use Xditn\Base\modules\System\Http\Controllers\RouteController;
use Xditn\Base\modules\System\Http\Controllers\SchemaController;
use Xditn\Base\modules\System\Http\Controllers\SmsConfigController;
use Xditn\Base\modules\System\Http\Controllers\SystemAttachmentCategoryController;
use Xditn\Base\modules\System\Http\Controllers\SystemAttachmentsController;
use Xditn\Base\modules\System\Http\Controllers\SystemSmsCodeController;
use Xditn\Base\modules\System\Http\Controllers\SystemSmsTemplateController;
use Xditn\Base\modules\System\Http\Controllers\UploadConfigController;
use Xditn\Base\modules\System\Http\Controllers\WebhookController;
use Xditn\Base\modules\System\Http\Controllers\WechatConfigController;

Route::prefix('system')->group(function () {
    Route::apiResource('dictionary', DictionaryController::class);
    Route::put('dictionary/enable/{id}', [DictionaryController::class, 'enable']);
    Route::post('dictionary/enums/{id}', [DictionaryController::class, 'enums']);

    Route::apiResource('dic/values', DictionaryValuesController::class);
    Route::put('dic/values/enable/{id}', [DictionaryValuesController::class, 'enable']);
    // 定时任务
    Route::apiResource('cron/tasks', CronTasksController::class);
    Route::apiResource('cron/log', CronTasksLogController::class)->only(['index', 'destroy']);
    // token管理
    Route::apiResource('personal/access/tokens', PersonalAccessTokensController::class)->only(['index', 'destroy']);
    // 异步任务
    Route::apiResource('async/task', AsyncTaskController::class)->only(['index', 'destroy']);
    // 上传管理
    Route::post('upload/config', [UploadConfigController::class, 'store']);
    Route::get('upload/config/{driver?}', [UploadConfigController::class, 'show']);
    // 短信配置
    Route::post('sms/config', [SmsConfigController::class, 'store']);
    Route::get('sms/config/{driver?}', [SmsConfigController::class, 'show']);
    // 短信模板
    Route::apiResource('sms/template', SystemSmsTemplateController::class);
    Route::apiResource('system/sms/code', SystemSmsCodeController::class)->only(['index', 'destroy']);

    // 微信配置
    Route::post('wechat/config', [WechatConfigController::class, 'store']);
    Route::get('wechat/config/{driver}', [WechatConfigController::class, 'show']);

    // 附件管理
    Route::apiResource('attachments', SystemAttachmentsController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('attachment/category', SystemAttachmentCategoryController::class)->except(['show']);
    // webhook 通知
    Route::apiResource('webhook', WebhookController::class);
    Route::put('webhook/enable/{id}', [WebhookController::class, 'enable']);
    Route::get('webhook/test/{id}', [WebhookController::class, 'test']);
    // 域名配置
    Route::apiResource('domain/config', DomainConfigController::class)->only(['index', 'store']);

    Route::controller(RouteController::class)->group(function () {
        Route::get('route', 'index');
        Route::put('route/cache', 'cache');
    });

    Route::get('connector/log', [ConnectorLogController::class, 'index']);
    Route::match(['get', 'post'], 'connector/config', [ConnectorLogController::class, 'config']);
    Route::get('connector/summary', [ConnectorLogController::class, 'summary']); // 接口聚合
    Route::get('connector/status/code', [ConnectorLogController::class, 'statusCode']); // 接口状态统计
    Route::get('connector/time/taken', [ConnectorLogController::class, 'timeTaken']); // 接口耗时统计
    Route::get('connector/requests/top10', [ConnectorLogController::class, 'requestsTop10']); // 接口请求量统计
    Route::get('connector/requests/errors/top10', [ConnectorLogController::class, 'requestErrorsTop10']); // 接口请求错误统计
    Route::get('connector/requests/fast/top10', [ConnectorLogController::class, 'requestFastTop10']); // 接口请求最快统计
    Route::get('connector/requests/slow/top10', [ConnectorLogController::class, 'requestSlowTop10']); // 接口请求最慢统计
    Route::get('connector/requests/every/hour', [ConnectorLogController::class, 'everyHourRequests']); // 每小时请求统计
    Route::get('connector/requests/every/minute', [ConnectorLogController::class, 'everyMinuteRequests']); // 每分钟请求统计

    // 数据表管理
    Route::get('schema', [SchemaController::class, 'index']);
    // 数据表字段
    Route::get('schema/fields/{table}', [SchemaController::class, 'fields']);
    // 添加角色与字段关联
    Route::post('schema/fields/role/visible', [SchemaController::class, 'fieldsRoleVisible']);

    Route::get('schema/field/management', [SchemaController::class, 'fieldsManage']);
    Route::delete('schema/field/management/{id}', [SchemaController::class, 'destroyField']);
    //next
});
