<?php

namespace Xditn\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Finder\Finder;
use Xditn\MModule;

abstract class XditnModuleServiceProvider extends ServiceProvider
{
    // 事件列表
    protected array $events = [];

    protected array $commands = [];

    /**
     * 注册服务
     *
     * @return void
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function register(): void
    {
        // 注册事件监听
        foreach ($this->events as $event => $listener) {
            Event::listen($event, $listener);
        }
        // 加载中间件
        $this->loadMiddlewares();
        // 加载模块路由
        $this->loadModuleRoute();
        // 加载配置
        $this->loadConfig();

        $this->commands($this->commands);
    }

    /**
     * 加载中间件
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function loadMiddlewares(): void
    {
        // 获取中间件列表
        $middlewares = $this->middlewares();
        if (! empty($middlewares)) {
            // 获取当前路由中间件配置
            $route = $this->app['config']->get('xditn.route', [
                'middlewares' => [],
            ]);
            // 合并中间件
            $route['middlewares'] = array_merge($route['middlewares'], $middlewares);
            // 设置新的路由中间件配置
            $this->app['config']->set('xditn.route', $route);
        }
    }

    // 加载模块配置
    protected function loadConfig(): void
    {
        // 获取模块配置路径
        $configPath = $this->configPath();
        if (! is_dir($configPath)) {
            return;
        }
        // 遍历配置目录下的所有文件
        $files = [];
        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            $files[str_replace('.php', '', $file->getBasename())] = $file->getRealPath();
        }
        // 加载多个配置文件
        foreach ($files as $name => $file) {
            $this->app->make('config')->set(sprintf('%s.%s', $this->moduleName(), $name), require $file);
        }
    }

    // 返回中间件列表（默认为空）
    protected function middlewares(): array
    {
        return [];
    }

    /**
     * 加载模块路由
     * return void
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function loadModuleRoute(): void
    {
        // 获取当前模块路由配置
        $routes = $this->app['config']->get('xditn.module.routes', []);
        // 添加当前模块的路由路径
        $routes[] = MModule::getModuleRoutePath($this->moduleName());
        // 设置新的模块路由配置
        $this->app['config']->set('xditn.module.routes', $routes);
    }

    // 抽象方法：返回模块名称
    abstract protected function moduleName(): string | array;

    // 返回模块配置路径
    protected function configPath(): string
    {
        return MModule::getModulePath($this->moduleName()).'config'.DIRECTORY_SEPARATOR;
    }
}
