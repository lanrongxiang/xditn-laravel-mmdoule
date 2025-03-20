<?php

declare(strict_types=1);

namespace Xditn\Support\Module\Driver;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Xditn\Contracts\ModuleRepositoryInterface;
use Xditn\Exceptions\FailedException;
use Xditn\MModule;

/**
 * FileDriver 用于处理模块的增删改查、启用/禁用等操作，模块数据存储在 modules.json 文件中
 */
class FileDriver implements ModuleRepositoryInterface
{
    protected string $moduleJson;

    /**
     * 构造函数，初始化模块 JSON 文件路径
     */
    public function __construct()
    {
        $this->moduleJson = storage_path('app').DIRECTORY_SEPARATOR.'modules.json';
    }

    /**
     * 获取所有模块信息，支持根据模块标题进行搜索
     *
     * @param  array  $search 搜索条件
     * @return Collection 模块集合
     */
    public function all(array $search = []): Collection
    {
        if (! File::exists($this->moduleJson) || empty(File::get($this->moduleJson))) {
            return collect([]);
        }

        $modules = collect(json_decode(File::get($this->moduleJson), true));

        return $search['title'] ?? ''
            ? $modules->filter(fn ($module) => Str::contains($module['title'], $search['title']))
            : $modules->values();
    }

    /**
     * 创建新模块，并将其写入模块文件中
     *
     * @param  array  $module 模块数据
     * @return bool
     */
    public function create(array $module): bool
    {
        $modules = $this->all();
        $this->hasSameModule($module, $modules);
        $module = array_merge([
            'provider' => '\\'.MModule::getModuleServiceProvider($module['path']),
            'version' => '1.0.0',
            'enable' => true,
        ], $module);
        $this->removeDirs($module);

        return File::put($this->moduleJson, $modules->push($module)->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }

    /**
     * 根据模块名获取模块信息
     *
     * @param  string  $name 模块名称
     * @return Collection 模块信息
     *
     * @throws FailedException 模块未找到时抛出异常
     */
    public function show(string $name): Collection
    {
        $module = $this->all()->first(fn ($module) => $module['name'] === $name);

        return $module ? collect($module) : throw new FailedException("Module [$name] not found");
    }

    /**
     * 更新模块信息
     *
     * @param  string  $name 模块名称
     * @param  array  $module 更新的数据
     * @return bool
     */
    public function update(string $name, array $module): bool
    {
        return File::put(
                $this->moduleJson,
                $this->all()->map(function ($m) use ($module, $name) {
                    if ($m['name'] === $name) {
                        return array_merge($m, $module);
                    }
                    $this->removeDirs($m);

                    return $m;
                })->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            ) !== false;
    }

    /**
     * 删除指定模块
     *
     * @param  string  $name 模块名称
     * @return bool
     */
    public function delete(string $name): bool
    {
        return File::put(
                $this->moduleJson,
                $this->all()->reject(fn ($module) => $module['name'] === $name)->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            ) !== false;
    }

    /**
     * 启用或禁用模块
     *
     * @param  string  $name 模块名称
     * @return bool
     */
    public function disOrEnable(string $name): bool
    {
        return File::put(
                $this->moduleJson,
                $this->all()->map(function ($module) use ($name) {
                    if ($module['name'] === $name) {
                        $module['enable'] = ! $module['enable'];
                    }

                    return $module;
                })->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            ) !== false;
    }

    /**
     * 获取所有启用的模块
     *
     * @return Collection 启用的模块集合
     */
    public function getEnabled(): Collection
    {
        return $this->all()->where('enable', true);
    }

    /**
     * 检查指定模块是否已启用
     *
     * @param  string  $moduleName 模块名称
     * @return bool
     */
    public function enabled(string $moduleName): bool
    {
        return $this->getEnabled()->pluck('name')->contains($moduleName);
    }

    /**
     * 检查是否存在相同模块
     *
     * @param  array  $module 模块数据
     * @param  Collection  $modules 所有模块集合
     *
     * @throws FailedException 如果模块已存在则抛出异常
     */
    protected function hasSameModule(array $module, Collection $modules): void
    {
        if ($modules->pluck('name')->contains($module['name'])) {
            throw new FailedException(sprintf('Module [%s] has been created', $module['name']));
        }
    }

    /**
     * 移除模块数据中的 dirs 字段
     *
     * @param  array  $modules 模块数据
     */
    protected function removeDirs(array &$modules): void
    {
        if ($modules['dirs'] ?? false) {
            unset($modules['dirs']);
        }
    }
}
