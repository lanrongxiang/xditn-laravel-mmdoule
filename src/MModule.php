<?php

namespace Xditn;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Xditn\Contracts\ModuleRepositoryInterface;
use Xditn\Support\Module\Installer;

class MModule
{
    public const  VERSION = '0.1.0';

    // 获取版本
    public static function version(): string
    {
        return self::VERSION;
    }

    // 获取模块根目录配置
    public static function moduleRoot(): string
    {
        return config('xditn.module.root', 'modules');
    }

    // 获取模块根目录路径
    public static function moduleRootPath(): string
    {
        return self::makeDir(base_path(self::moduleRoot()).DIRECTORY_SEPARATOR);
    }

    // 创建目录
    public static function makeDir(string $dir): string
    {
        if (! File::isDirectory($dir) && ! File::makeDirectory($dir, 0777, true)) {
            throw new \RuntimeException(sprintf('目录 %s 创建失败', $dir));
        }

        return $dir;
    }

    // 获取模块路径
    public static function getModulePath(string $module, bool $make = true): string
    {
        $path = self::moduleRootPath().ucfirst($module).DIRECTORY_SEPARATOR;

        return $make ? self::makeDir($path) : $path;
    }

    // 删除模块路径
    public static function deleteModulePath(string $module): bool
    {
        if (self::isModulePathExist($module)) {
            File::deleteDirectory(self::getModulePath($module));
        }

        return true;
    }

    // 判断模块路径是否存在
    public static function isModulePathExist(string $module): bool
    {
        return File::isDirectory(self::getModulePath($module, false));
    }

    // 获取模块迁移目录
    public static function getModuleMigrationPath(string $module): string
    {
        return self::makeDir(
            self::getModulePath($module).'database'.DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR
        );
    }

    // 获取模块Seeder目录
    public static function getModuleSeederPath(string $module): string
    {
        return self::makeDir(
            self::getModulePath($module).'database'.DIRECTORY_SEPARATOR.'seeder'.DIRECTORY_SEPARATOR
        );
    }

    // 获取所有模块目录
    public static function getModulesPath(): array
    {
        return File::directories(self::moduleRootPath());
    }

    // 获取模块根命名空间
    public static function getModuleRootNamespace(): string
    {
        return config('xditn.module.namespace', 'Modules').'\\';
    }

    // 获取模块命名空间
    public static function getModuleNamespace(string $moduleName): string
    {
        return self::getModuleRootNamespace().ucfirst($moduleName).'\\';
    }

    // 获取模块模型命名空间
    public static function getModuleModelNamespace(string $moduleName): string
    {
        return self::getModuleNamespace($moduleName).'Models\\';
    }

    // 获取模块服务提供者命名空间
    public static function getModuleServiceProviderNamespace(string $moduleName): string
    {
        return self::getModuleNamespace($moduleName).'Providers\\';
    }

    // 获取模块服务提供者类名
    public static function getModuleServiceProvider(string $moduleName): string
    {
        return self::getModuleServiceProviderNamespace($moduleName).ucfirst($moduleName).'ServiceProvider';
    }

    // 获取模块控制器命名空间
    public static function getModuleControllerNamespace(string $moduleName): string
    {
        return self::getModuleNamespace($moduleName).'Http\\Controllers\\';
    }

    // 获取模块请求命名空间
    public static function getModuleRequestNamespace(string $moduleName): string
    {
        return self::getModuleNamespace($moduleName).'Http\\Requests\\';
    }

    // 获取模块事件命名空间
    public static function getModuleEventsNamespace(string $moduleName): string
    {
        return self::getModuleNamespace($moduleName).'Events\\';
    }

    // 获取模块监听器命名空间
    public static function getModuleListenersNamespace(string $moduleName): string
    {
        return self::getModuleNamespace($moduleName).'Listeners\\';
    }

    // 获取模块提供者目录
    public static function getModuleProviderPath(string $module): string
    {
        return self::makeDir(self::getModulePath($module).'Providers'.DIRECTORY_SEPARATOR);
    }

    // 获取模块模型目录
    public static function getModuleModelPath(string $module): string
    {
        return self::makeDir(self::getModulePath($module).'Models'.DIRECTORY_SEPARATOR);
    }

    // 获取模块控制器目录
    public static function getModuleControllerPath(string $module): string
    {
        return self::makeDir(
            self::getModulePath($module).'Http'.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR
        );
    }

    // 获取模块请求目录
    public static function getModuleRequestPath(string $module): string
    {
        return self::makeDir(
            self::getModulePath($module).'Http'.DIRECTORY_SEPARATOR.'Requests'.DIRECTORY_SEPARATOR
        );
    }

    // 获取模块事件目录
    public static function getModuleEventPath(string $module): string
    {
        return self::makeDir(self::getModulePath($module).'Events'.DIRECTORY_SEPARATOR);
    }

    // 获取模块监听器目录
    public static function getModuleListenersPath(string $module): string
    {
        return self::makeDir(self::getModulePath($module).'Listeners'.DIRECTORY_SEPARATOR);
    }

    // 获取模块命令目录
    public static function getCommandsPath(string $module): string
    {
        return self::makeDir(self::getModulePath($module).'Commands'.DIRECTORY_SEPARATOR);
    }

    // 获取模块命令命名空间
    public static function getCommandsNamespace(string $module): string
    {
        return self::getModuleNamespace($module).'Commands\\';
    }

    // 获取模块路由路径
    public static function getModuleRoutePath(string $module, string $routeName = 'route.php'): string
    {
        $path = self::getModulePath($module).'routes'.DIRECTORY_SEPARATOR;
        self::makeDir($path);

        return $path.$routeName;
    }

    // 判断模块路由文件是否存在
    public static function isModuleRouteExists(string $module): bool
    {
        return File::exists(self::getModuleRoutePath($module));
    }

    // 获取模块视图目录
    public static function getModuleViewsPath(string $module): string
    {
        return self::makeDir(self::getModulePath($module).'views'.DIRECTORY_SEPARATOR);
    }

    // 获取相对路径
    public static function getModuleRelativePath(string $path): string
    {
        return Str::replaceFirst(base_path(), '.', $path);
    }

    // 获取模块安装器
    public static function getModuleInstaller(string $module): Installer
    {
        $installer = self::getModuleNamespace($module).'Installer';
        if (class_exists($installer)) {
            return app($installer);
        }
        throw new \RuntimeException("安装器 [$installer] 未找到");
    }

    // 从路由动作中解析模块
    public static function parseFromRouteAction(): array
    {
        [$controllerNamespace, $action] = explode('@', Route::currentRouteAction());
        $controllerNamespace = Str::of($controllerNamespace)->lower()->remove('controller')->explode('\\');
        $controller = $controllerNamespace->pop();
        $module = $controllerNamespace->get(1);

        return [$module, $controller, $action];
    }

    /**
     * 获取控制器中的所有公共方法（动作）
     *
     * @param  string  $module 模块名称
     * @param  string  $controller 控制器名称
     * @return array 控制器中的公共方法列表
     *
     * @throws \ReflectionException 如果类不存在或无法反射，则抛出异常
     */
    public static function getControllerActions(string $module, string $controller): array
    {
        // 获取控制器的完整命名空间路径
        $controllerNamespace = self::getModuleControllerNamespace($module);
        $controllerClass = $controllerNamespace.ucfirst($controller).'Controller';
        // 通过反射获取控制器类的所有方法
        $reflectionClass = new \ReflectionClass($controllerClass);
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        // 过滤掉构造函数和其他非动作方法
        $actions = array_filter($methods, fn ($method) => ! $method->isConstructor());
        $actions = array_map(fn ($method) => $method->getName(), $actions);

        return $actions;
    }

    /**
     * 获取路由缓存文件路径
     *
     * @return string 路由缓存文件路径
     */
    public static function getRouteCachePath(): string
    {
        // 从配置中获取缓存路径，默认为 base_path('bootstrap/cache') 下的 'admin_route_cache.php'
        return config(
            'xditn.route.cache_path',
            base_path('bootstrap/cache').DIRECTORY_SEPARATOR.'admin_route_cache.php'
        );
    }

    public static function deleteConfigFile(): bool
    {
        $configFilePath = config_path('xditn.php');
        if (File::exists($configFilePath)) {
            return File::delete($configFilePath);
        }

        return false; // 文件不存在
    }

    public static function deleteApiConfigFile(): bool
    {
        $configFilePath = config_path('xditn_api_doc.php');
        if (File::exists($configFilePath)) {
            return File::delete($configFilePath);
        }

        return false; // 文件不存在
    }

    public static function getAllModules(): mixed
    {
        return app()->make(ModuleRepositoryInterface::class)->all();
    }
}
