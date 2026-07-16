<?php

use Xditn\Listeners\RequestHandledListener;
use Xditn\Middleware\AuthMiddleware;
use Xditn\Middleware\JsonResponseMiddleware;

return [
    'super_admin' => 1,

    'request_allowed' => true,

    'module' => [
        'root' => 'modules',
        'namespace' => 'Modules',
        'default' => [],
        'default_dirs' => [
            'Http'.DIRECTORY_SEPARATOR,
            'Http'.DIRECTORY_SEPARATOR.'Requests'.DIRECTORY_SEPARATOR,
            'Http'.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR,
            'Models'.DIRECTORY_SEPARATOR,
        ],
        'driver' => [
            'default' => env('XDITN_MODULE_DRIVER', 'database'),
            'table_name' => env('XDITN_MODULE_TABLE', 'admin_modules'),
        ],
        'routes' => [],
        'autoload' => env('XDITN_MODULE_AUTOLOAD', true),
    ],

    'always_json' => true,

    'response' => [
        'always_json' => JsonResponseMiddleware::class,
        'request_handled_listener' => RequestHandledListener::class,
    ],

    'listen_db_log' => env('APP_DEBUG', true),

    'auth_model' => env('XDITN_AUTH_MODEL', \Modules\User\Models\User::class),

    'auth' => 'admin',

    'route' => [
        'prefix' => 'api',
        'middlewares' => [
            AuthMiddleware::class,
            JsonResponseMiddleware::class,
        ],
    ],

    'views_path' => base_path(
        'web'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR
    ),

    'system_api_log' => env('XDITN_SYSTEM_API_LOG', false),

    'image' => [
        'driver' => env('XDITN_IMAGE_DRIVER', 'gd'),
        'options' => [],
        'read_from' => env('XDITN_IMAGE_READ_FROM', 'uploads'),
    ],

    'admin_cache_key' => env('XDITN_ADMIN_CACHE_KEY', 'admin_dashboard_'),

    'model' => [
        'date_format' => 'Y-m-d H:i:s',
    ],

    'excel' => [
        'export_path' => 'excel/export',
    ],
];
