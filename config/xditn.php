<?php

use Modules\Member\Models\Members;
use Modules\User\Models\User;
use Xditn\Listeners\RequestHandledListener;
use Xditn\Middleware\AuthMiddleware;
use Xditn\Middleware\JsonResponseMiddleware;

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
        'always_json' => JsonResponseMiddleware::class,  // 始终使用 JSON 响应中间件
        'request_handled_listener' => RequestHandledListener::class,  // 请求处理事件监听器
    ],
    // 数据库 SQL 日志监听
    'listen_db_log' => env('APP_DEBUG', true),
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
                'model' => User::class,
            ],
            // 前台用户模型
            'app_users' => [
                'driver' => 'eloquent',
                'model' => Members::class,
            ],
        ],
    ],
    // 路由配置
    'route' => [
        'prefix' => 'api',  // 路由前缀
        'middlewares' => [  // 路由中间件
            AuthMiddleware::class,
            JsonResponseMiddleware::class,
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
    /*
|--------------------------------------------------------------------------
| 图片处理
|
| 默认使用 GD
|--------------------------------------------------------------------------
*/
    'image' => [
        'driver' => env('CATCH_IMAGE_DRIVER', 'gd'),
        'options' => [],
        /**
         * 默认读取磁盘
         */
        'read_from' => env('CATCH_IMAGE_READ_FROM', 'uploads'),
    ],
    /*
    |--------------------------------------------------------------------------
    | 后台缓存统一管理
    |
    | 配置后台缓存前缀，便于清理后台管理的缓存
    |--------------------------------------------------------------------------
    */
    'admin_cache_key' => env('CATCH_ADMIN_CACHE_KEY', 'admin_dashboard_'),
    /*
    |--------------------------------------------------------------------------
    | 模型相关配置
    |
    | 配置模型的配置
    |--------------------------------------------------------------------------
    */
    'model' => [
        // created_at & updated_at format
        'date_format' => 'Y-m-d H:i:s',
    ],
    /*
    |--------------------------------------------------------------------------
    | Excel 配置
    |--------------------------------------------------------------------------
    */
    'excel' => [
        /**
         * 导出路径, 相对于 storage 目录得相对路径
         */
        'export_path' => 'excel/export',
    ],
];
