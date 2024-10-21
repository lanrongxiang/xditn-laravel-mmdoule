<?php

declare(strict_types=1);

namespace Xditn\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Routing\RouteCollection;
use Xditn\MModule;

class XditnRouteCache extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = 'xditn:route:cache';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '创建路由缓存文件以加快路由注册速度';

    /**
     * 文件系统实例
     *
     * @var Filesystem
     */
    protected Filesystem $files;

    /**
     * 管理路由集合
     *
     * @var RouteCollection
     */
    protected RouteCollection $adminRoutes;

    /**
     * 应用路由集合
     *
     * @var RouteCollection
     */
    protected RouteCollection $appRoutes;

    /**
     * 构造函数，初始化文件系统和路由集合
     *
     * @param  Filesystem  $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
        $this->adminRoutes = new RouteCollection();
        $this->appRoutes = new RouteCollection();
    }

    /**
     * 执行命令的主要逻辑
     *
     * @return void
     */
    public function handle(): void
    {
        $this->callSilent('xditn:route:clear');       // 清除旧的路由缓存
        $routes = $this->getFreshApplicationRoutes(); // 获取新应用的路由
        foreach ($routes as $route) {
            // 根据控制器的命名空间将路由分类到管理路由或应用路由
            $target = str_starts_with($route->action['controller'], 'Modules') ? $this->adminRoutes : $this->appRoutes;
            $target->add($route);
        }
        // 缓存管理路由和应用路由
        $this->cacheRoutes($this->adminRoutes, $this->getAdminRouteCachePath(), 'Admin Routes');
        $this->cacheRoutes($this->appRoutes, $this->laravel->getCachedRoutesPath(), 'App Routes');
    }

    /**
     * 缓存给定的路由集合到指定路径
     *
     * @param  RouteCollection  $routes
     * @param  string  $path
     * @param  string  $type
     * @return void
     */
    protected function cacheRoutes(RouteCollection $routes, string $path, string $type): void
    {
        if (count($routes) === 0) { // 使用 count() 方法判断是否为空
            $this->components->error("您的应用没有任何 $type");

            return;
        }
        // 准备路由以便序列化
        foreach ($routes as $route) {
            $route->prepareForSerialization();
        }
        // 写入缓存文件
        $this->files->put($path, $this->buildRouteCacheFile($routes));
        $this->components->info("$type 缓存成功。");
    }

    /**
     * 获取新应用的路由集合
     *
     * @return RouteCollection
     */
    protected function getFreshApplicationRoutes(): RouteCollection
    {
        $app = $this->getFreshApplication(); // 获取新应用实例
        $routes = $app['router']->getRoutes();
        // 刷新路由的名称和动作查找
        $routes->refreshNameLookups();
        $routes->refreshActionLookups();

        return $routes; // 直接返回路由集合
    }

    /**
     * 获取新应用实例
     *
     * @return Application
     */
    protected function getFreshApplication(): Application
    {
        $app = require $this->laravel->bootstrapPath('app.php');
        $app->make(ConsoleKernelContract::class)->bootstrap();

        return $app; // 返回应用实例
    }

    /**
     * 构建路由缓存文件的内容
     *
     * @param  RouteCollection  $routes
     * @return string
     */
    protected function buildRouteCacheFile(RouteCollection $routes): string
    {
        $stub = <<<'TEXT'
<?php

/*
|--------------------------------------------------------------------------
| Load The Cached Routes
|--------------------------------------------------------------------------
*/

app('router')->setCompiledRoutes(
    {{routes}}
);
TEXT;

        return str_replace('{{routes}}', var_export($routes->compile(), true), $stub);
    }

    /**
     * 获取管理路由缓存路径
     *
     * @return string
     */
    protected function getAdminRouteCachePath(): string
    {
        return MModule::getRouteCachePath();
    }
}
