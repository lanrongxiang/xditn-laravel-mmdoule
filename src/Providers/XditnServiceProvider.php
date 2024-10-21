<?php

namespace Xditn\Providers;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Xditn\Contracts\ModuleRepositoryInterface;
use Xditn\Exceptions\Handler;
use Xditn\Support\DB\Query;
use Xditn\Support\Macros\MacrosRegister;
use Xditn\Support\Module\ModuleManager;
use Xditn\MModule;

class XditnServiceProvider extends ServiceProvider
{
    /**
     * 启动服务提供者
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        // 启动默认模块提供者
        $this->bootDefaultModuleProviders();
        // 启动模块提供者
        $this->bootModuleProviders();
        // 注册事件
        $this->registerEvents();
        // 监听数据库日志
        $this->listenDBLog();
        // 启动宏注册器
        $this->app->make(MacrosRegister::class)->boot();
    }

    /**
     * 注册服务提供者
     *
     * @return void
     * @throws \ReflectionException
     */
    public function register(): void
    {
        // 注册命令
        $this->registerCommands();
        // 注册模块仓库
        $this->registerModuleRepository();
        // 注册异常处理器
        $this->registerExceptionHandler();
        // 发布配置文件
        $this->publishConfig();
        // 发布模块迁移文件
        $this->publishModuleMigration();
    }

    /**
     * 注册命令
     *
     * @return void
     * @throws \ReflectionException
     */
    protected function registerCommands(): void
    {
        // 加载命令
        loadCommands(dirname(__DIR__) . '/Commands', 'Xditn\\');
    }

    /**
     * 注册模块仓库
     *
     * @return void
     */
    protected function registerModuleRepository(): void
    {
        // 单例模式注册模块管理器
        $this->app->singleton(ModuleManager::class, function ()
        {
            return new ModuleManager(fn() => Container::getInstance());
        });
        // 单例模式注册模块仓库
        $this->app->singleton(ModuleRepositoryInterface::class, function ()
        {
            return $this->app->make(ModuleManager::class)->driver();
        });
        // 创建模块仓库别名
        $this->app->alias(ModuleRepositoryInterface::class, 'module');
    }

    /**
     * 注册事件
     *
     * @return void
     */
    protected function registerEvents(): void
    {
        // 监听 HTTP 请求处理完成事件
        Event::listen(RequestHandled::class, config('xditn.response.request_handled_listener'));
    }

    /**
     * 注册异常处理器
     *
     * @return void
     */
    protected function registerExceptionHandler(): void
    {
        // 如果请求来自控制面板，则注册自定义异常处理器
        if (isRequestFromDashboard()) {
            $this->app->singleton(ExceptionHandler::class, Handler::class);
        }
    }

    /**
     * 发布配置文件
     *
     * @return void
     */
    protected function publishConfig(): void
    {
        // 仅在控制台运行时发布配置文件
        if ($this->app->runningInConsole()) {
            $this->publishes([dirname(__DIR__, 2) . '/config/xditn.php' => config_path('xditn.php')], 'xditn-config');
        }
    }

    /**
     * 发布模块迁移文件
     *
     * @return void
     */
    protected function publishModuleMigration(): void
    {
        // 仅在控制台运行时发布模块迁移文件
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    dirname(__DIR__, 2) . '/database/migrations/2022_11_14_034127_module.php' => database_path(
                        'migrations/2022_11_14_034127_module.php'
                    ),
                ],
                'xditn-module'
            );
        }
    }

    /**
     * 启动默认模块提供者
     *
     * @return void
     */
    protected function bootDefaultModuleProviders(): void
    {
        // 获取默认模块，并注册其服务提供者
        foreach ($this->app['config']->get('xditn.module.default', []) as $module) {
            $provider = MModule::getModuleServiceProvider($module);
            if (class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }

    /**
     * 启动已启用的模块
     *
     * @return void
     * @throws BindingResolutionException
     */
    protected function bootModuleProviders(): void
    {
        // 获取启用的模块，并注册其服务提供者
        foreach ($this->app->make(ModuleRepositoryInterface::class)->getEnabled() as $module) {
            if (class_exists($module['provider'])) {
                $this->app->register($module['provider']);
            }
        }
        // 注册模块路由
        $this->registerModuleRoutes();
    }

    /**
     * 注册模块路由
     *
     * @return void
     */
    protected function registerModuleRoutes(): void
    {
        // 如果路由未缓存，则注册模块路由
        if (!$this->app->routesAreCached()) {
            $route = $this->app['config']->get('xditn.route');
            if (!empty($route)) {
                Route::prefix($route['prefix'] ?? 'api')->middleware($route['middlewares'])->group(
                    $this->app['config']->get('xditn.module.routes')
                );
            }
        }
    }

    /**
     * 监听数据库日志
     *
     * @return void
     */
    protected function listenDBLog(): void
    {
        if ($this->app['config']->get('xditn.listen_db_log')) {
            // 开启数据库查询监听
            Query::listen();
            // 在应用终止时记录查询日志
            $this->app->terminating(function ()
            {
                Query::log();
            });
        }
    }
}
