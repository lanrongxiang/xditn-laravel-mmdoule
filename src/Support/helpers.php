<?php

declare(strict_types=1);

use Illuminate\Console\Application;
use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use Illuminate\Foundation\Vite;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
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
        $paths = Collection::make(Arr::wrap($paths))->unique()->filter(fn ($path) => is_dir($path));
        if ($paths->isEmpty()) {
            return;
        }
        foreach ((new Finder())->in($paths->toArray())->files() as $command) {
            $commandClass = $namespace.str_replace(['/', '.php'],
                    ['\\', ''],
                    Str::after($command->getRealPath(), $searchPath));
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
 * @param  array  $data       树形数据
 * @param  string  $table      表名
 * @param  string  $pid        父ID字段名
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
            $existing = $model->setTable($table)->where('permission_name', $value['permission_name'])->where(
                    'module',
                    $value['module']
                )->where('permission_mark', $value['permission_mark'])->first();
            $id = $existing ? $existing->id : DB::table($table)->insertGetId($value);
            if ($children) {
                array_walk($children, fn (&$v) => $v[$pid] = $id);
                importTreeData($children, $table, $pid);
            }
        }
    }
}
/**
 * 判断请求是否来自Request-from
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
/**
 * 执行系统命令
 *
 * 支持单条命令或多条命令的执行，针对不同操作系统进行区分处理。
 *
 * @param  string|array  $command 要执行的命令
 * @return void
 */
if (! function_exists('command')) {
    function command(string|array $command): void
    {
        $exec = function ($command) {
            $formattedCommand = Application::formatCommandString($command);
            // 针对 macOS 系统执行不同的处理
            if (Str::of(PHP_OS)->lower()->contains('dar')) {
                exec($formattedCommand);
                exec($formattedCommand); // 再次执行确保命令完成
            } else {
                // 其他系统使用 Process 运行命令，并处理异常
                Process::run($formattedCommand)->throw();
                Process::run($formattedCommand)->throw();
            }
        };
        // 处理单条命令或多条命令的情况
        if (is_array($command)) {
            foreach ($command as $c) {
                $exec($c);
            }
        } else {
            $exec($command);
        }
    }
}
/**
 * 加载 CMS 资源
 *
 * 使用 Vite 进行 CMS 资源的加载。
 *
 * @param  string  $asset 资源路径
 * @return mixed 返回 Vite 生成的资源 URL
 */
if (! function_exists('cms_asset')) {
    function cms_asset(string $asset)
    {
        return Vite::cmsAsset($asset);
    }
}
/**
 * 格式化返回数据
 *
 * 支持分页数据和普通数据的统一格式化处理，确保返回结构一致。
 *
 * @param  mixed  $data 要格式化的数据
 * @return array 格式化后的数组数据
 */
if (! function_exists('format_response_data')) {
    function format_response_data(mixed $data): array
    {
        $responseData = [];
        // 如果数据是分页数据实例
        if ($data instanceof LengthAwarePaginator) {
            $responseData = [
                'data' => $data->items(),
                'total' => $data->total(),
                'limit' => $data->perPage(),
                'page' => $data->currentPage(),
            ];
        } // 如果数据是对象且包含分页信息
        elseif (is_object($data) && property_exists($data, 'per_page') && property_exists(
                $data,
                'total'
            ) && property_exists($data, 'current_page')) {
            $responseData = [
                'data' => $data->data,
                'total' => $data->total,
                'limit' => $data->per_page,
                'page' => $data->current_page,
            ];
        } // 其他情况直接返回数据
        else {
            $responseData['data'] = $data;
        }

        return $responseData;
    }
}

/**
 * 获取指定目录下的所有子目录名
 *
 * @param  string  $directory 目录路径
 * @return array 返回子目录名数组
 */
if (! function_exists('getSubdirectory')) {
    function getSubdirectories(string $directory): array
    {
        $subdirectories = [];

        // 获取所有文件和目录
        $items = scandir($directory);

        foreach ($items as $item) {
            // 排除 '.' 和 '..'
            if ($item !== '.' && $item !== '..') {
                $path = $directory.DIRECTORY_SEPARATOR.$item;

                // 检查是否为目录
                if (is_dir($path)) {
                    $subdirectories[] = $item;
                }
            }
        }

        return $subdirectories;
    }

}

/**
 * 根据传入的优先元素排序数组，将指定元素放在数组的开头，并保持其他元素的原顺序。
 *
 * @param  array  $priorities 需要优先排列的元素数组（小写形式）。
 * @param  array  $items       原始的元素数组。
 * @return array             排序后的数组。
 */
if (! function_exists('getSubdirectory')) {
    function sortArrayByPriorities(array $priorities, array $items): array
    {
        // 将所有优先元素和原始元素都转换为小写，用于无视大小写的对比
        $priorities = array_map('strtolower', $priorities);
        $itemsLower = array_map('strtolower', $items);

        $sorted = [];  // 存储优先排列的元素
        $remaining = [];  // 存储未优先的元素

        foreach ($items as $key => $item) {
            $lowerItem = strtolower($item);  // 将当前元素转换为小写进行对比

            if (in_array($lowerItem, $priorities, true)) {
                $sorted[$lowerItem] = $item;  // 保留原始大小写，存储在优先数组中
            } else {
                $remaining[] = $item;  // 未匹配的元素存入剩余数组
            }
        }

        // 从优先数组中按传入的顺序获取存在的元素
        $finalSorted = [];
        foreach ($priorities as $priority) {
            if (isset($sorted[$priority])) {
                $finalSorted[] = $sorted[$priority];
            }
        }

        // 返回优先数组在前，剩余数组在后的合并结果
        return array_merge($finalSorted, $remaining);
    }
}
