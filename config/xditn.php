<?php

return [
    // 超级管理员 ID 配置
    'super_admin' => 1,
    /*
    |--------------------------------------------------------------------------
    |  请求允许
    |--------------------------------------------------------------------------
    |
    | 默认允许 GET 请求通过 RBAC 权限
    |
    |--------------------------------------------------------------------------
    */
    'request_allowed' => true,
    // 模块配置
    'module' => [
        'root' => 'modules',  // 模块根目录
        'namespace' => 'Xditn\Base\Modules',  // 模块根命名空间
        'default' => ['develop', 'user', 'common'],  // 默认模块
        'default_dirs' => [  // 默认生成的目录结构
            'Http'.DIRECTORY_SEPARATOR,
            'Http'.DIRECTORY_SEPARATOR.'Requests'.DIRECTORY_SEPARATOR,
            'Http'.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR,
            'Models'.DIRECTORY_SEPARATOR,
        ],
        // 模块驱动配置，默认使用文件存储
        'driver' => [
            'default' => 'file',  // 默认使用文件驱动
            'table_name' => 'system_modules',  // 数据库存储模块信息的表名
        ],
        // 模块路由集合
        'routes' => [],
        /**
         * 模块是否自动加载
         *
         * 如果设置成 true，模块会自动全部加载
         */
        'autoload' => env('XDITN_MODULE_AUTOLOAD', true),
    ],
    //始终使用json响应全局异常
    'always_json' => true,
    // 响应配置
    'response' => [
        'always_json' => \Xditn\Middleware\JsonResponseMiddleware::class,  // 始终使用 JSON 响应中间件
        'request_handled_listener' => \Xditn\Listeners\RequestHandledListener::class,  // 请求处理事件监听器
    ],
    // 数据库 SQL 日志监听
    'listen_db_log' => true,
    // 管理员身份验证模型配置
    'auth_model' => modules\User\Models\User::class,
    'auth' => 'admin',
    'auths' => [
        'guards' => [
            // 后台 guard
            'admin' => [
                'driver' => 'sanctum',
                'provider' => 'admin_users',
            ],

            // 前台 app 接口认证
            'app' => [
                'driver' => 'sanctum',
                'provider' => 'app_users',
            ],
        ],
        'providers' => [
            // 后台用户模型
            'admin_users' => [
                'driver' => 'eloquent',
                'model' => \Modules\User\Models\User::class,
            ],

            // 前台用户模型
            'app_users' => [
                'driver' => 'eloquent',
                'model' => \Modules\Member\Models\Members::class,
            ],
        ],
    ],
    // 路由配置
    'route' => [
        'prefix' => 'api',  // 路由前缀
        'middlewares' => [  // 路由中间件
            \Xditn\Middleware\AuthMiddleware::class,
            \Xditn\Middleware\JsonResponseMiddleware::class,
        ],
    ],
    // 视图路径配置
    'views_path' => base_path(
        'web'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR
    ),
    /*
  |--------------------------------------------------------------------------
  | 开启系统接口日志分析
  |
  | 接口日志依赖 Redis，提高性能
  |--------------------------------------------------------------------------
  */
    'system_api_log' => env('XDITN_SYSTEM_API_LOG', false),
];
