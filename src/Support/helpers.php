<?php

declare(strict_types=1);

use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Xditn\Base\XditnModel;
use Xditn\MModule;

/**
 * 加载命令
 */
if (! function_exists('loadCommands')) {
    /**
     * 加载命令函数
     *
     * @param  array|string  $paths      命令文件路径
     * @param  string  $namespace  命令类命名空间
     * @param  string|null  $searchPath 搜索路径（可选）
     *
     * @throws ReflectionException
     */
    function loadCommands(array|string $paths, string $namespace, string $searchPath = null): void
    {
        $searchPath ??= dirname($paths).DIRECTORY_SEPARATOR;

        $paths = Collection::make(Arr::wrap($paths))
                           ->unique()
                           ->filter(fn ($path) => is_dir($path));

        if ($paths->isEmpty()) {
            return;
        }

        foreach ((new Finder())->in($paths->toArray())->files() as $command) {
            $commandClass = $namespace.str_replace(['/', '.php'], ['\\', ''], Str::after($command->getRealPath(), $searchPath));

            if (is_subclass_of($commandClass, Command::class) && ! (new ReflectionClass($commandClass))->isAbstract()) {
                Artisan::starting(fn ($artisan) => $artisan->resolve($commandClass));
            }
        }
    }
}

/**
 * 获取带表前缀的表名
 */
if (! function_exists('withTablePrefix')) {
    function withTablePrefix(string $table): string
    {
        return DB::connection()->getTablePrefix().$table;
    }
}

/**
 * 获取守卫名称
 */
if (! function_exists('getGuardName')) {
    function getGuardName(): string
    {
        return array_keys(config('xditn.auth.guards', []))[0] ?? 'sanctum';
    }
}

/**
 * 获取表的列
 */
if (! function_exists('getTableColumns')) {
    function getTableColumns(string $table): array
    {
        return array_column(DB::select('DESC '.withTablePrefix($table)), 'Field');
    }
}

/**
 * 调试函数，添加跨域头
 */
if (! function_exists('dd_')) {
    /**
     * 打印调试信息并结束程序
     *
     * @param  mixed  ...$vars 任意数量的变量
     * @return never
     */
    function dd_(...$vars): never
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *');

        dd(...$vars);
    }
}

/**
 * 获取认证用户模型
 */
if (! function_exists('getAuthUserModel')) {
    function getAuthUserModel(): mixed
    {
        return config('xditn.auth_model');
    }
}

/**
 * 导入树形数据
 *
 * @param  array  $data 树形数据
 * @param  string  $table 表名
 * @param  string  $pid 父ID字段名
 * @param  string  $primaryKey 主键字段名
 */
if (! function_exists('importTreeData')) {
    function importTreeData(array $data, string $table, string $pid = 'parent_id', string $primaryKey = 'id'): void
    {
        foreach ($data as $value) {
            if (isset($value[$primaryKey])) {
                unset($value[$primaryKey]);
            }
            $children = $value['children'] ?? false;
            unset($value['children']);

            $model = new class extends XditnModel
            {
            };
            $existing = $model->setTable($table)
                              ->where('permission_name', $value['permission_name'])
                              ->where('module', $value['module'])
                              ->where('permission_mark', $value['permission_mark'])
                              ->first();

            $id = $existing ? $existing->id : DB::table($table)->insertGetId($value);

            if ($children) {
                array_walk($children, fn (&$v) => $v[$pid] = $id);
                importTreeData($children, $table, $pid);
            }
        }
    }
}

/**
 * 判断请求是否来自控制面板
 */
if (! function_exists('isRequestFromDashboard')) {
    function isRequestFromDashboard(): bool
    {
        return Request::hasHeader('Request-from') && Str::lower(Request::header('Request-from')) === 'dashboard';
    }
}

/**
 * 加载缓存的管理员路由
 */
if (! function_exists('loadCachedAdminRoutes')) {
    function loadCachedAdminRoutes(): void
    {
        if (routesAreCached()) {
            if (app()->runningInConsole() || isRequestFromDashboard()) {
                require MModule::getRouteCachePath();
            }
        }
    }
}

/**
 * 判断路由是否缓存
 */
if (! function_exists('routesAreCached')) {
    function routesAreCached(): bool
    {
        return file_exists(MModule::getRouteCachePath());
    }
}
