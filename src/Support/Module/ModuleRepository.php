<?php

declare(strict_types=1);

namespace Xditn\Support\Module;

use Events\Events\Module\Updated;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Xditn\Contracts\ModuleRepositoryInterface;
use Xditn\Events\Module\Created;
use Xditn\Events\Module\Creating;
use Xditn\Events\Module\Deleted;
use Xditn\Events\Module\Updating;

/**
 * 模块仓库类，负责模块的增删改查等操作
 */
class ModuleRepository
{
    protected ModuleRepositoryInterface $moduleRepository;

    /**
     * 构造函数，依赖注入模块仓库接口
     *
     * @param  ModuleRepositoryInterface  $moduleRepository
     */
    public function __construct(ModuleRepositoryInterface $moduleRepository)
    {
        $this->moduleRepository = $moduleRepository;
    }

    /**
     * 获取所有模块
     *
     * @param  array  $search 搜索条件
     * @return Collection 模块集合
     */
    public function all(array $search): Collection
    {
        return $this->moduleRepository->all($search);
    }

    /**
     * 创建模块
     *
     * @param  array  $module 模块信息
     * @return bool 创建结果
     */
    public function create(array $module): bool
    {
        // 将模块路径的首字母小写作为模块名
        $module['name'] = lcfirst($module['path']);

        // 触发创建前事件
        Event::dispatch(new Creating($module));

        // 创建模块
        $this->moduleRepository->create($module);

        // 触发创建后事件
        Event::dispatch(new Created($module));

        return true;
    }

    /**
     * 获取模块信息
     *
     * @param  string  $name 模块名称
     * @return Collection 模块信息集合
     *
     * @throws Exception
     */
    public function show(string $name): Collection
    {
        return $this->moduleRepository->show($name);
    }

    /**
     * 更新模块信息
     *
     * @param  string  $name 模块名称
     * @param  array  $module 模块信息
     * @return bool 更新结果
     */
    public function update(string $name, array $module): bool
    {
        // 将模块路径的首字母小写作为模块名
        $module['name'] = lcfirst($module['path']);

        // 触发更新前事件
        Event::dispatch(new Updating($name, $module));

        // 更新模块
        $this->moduleRepository->update($name, $module);

        // 触发更新后事件
        Event::dispatch(new Updated($name, $module));

        return true;
    }

    /**
     * 删除模块
     *
     * @param  string  $name 模块名称
     * @return bool 删除结果
     *
     * @throws Exception
     */
    public function delete(string $name): bool
    {
        // 获取模块信息
        $module = $this->show($name);

        // 删除模块
        $this->moduleRepository->delete($name);

        // 触发删除事件
        Event::dispatch(new Deleted($module));

        return true;
    }

    /**
     * 启用或禁用模块
     *
     * @param  string  $name 模块名称
     * @return bool|int 启用或禁用结果
     */
    public function disOrEnable(string $name): bool|int
    {
        return $this->moduleRepository->disOrEnable($name);
    }

    /**
     * 获取启用的模块
     *
     * @return Collection 已启用模块集合
     */
    public function getEnabled(): Collection
    {
        return $this->moduleRepository->getEnabled();
    }

    /**
     * 判断模块是否启用
     *
     * @param  string  $moduleName 模块名称
     * @return bool 是否启用
     */
    public function enabled(string $moduleName): bool
    {
        return $this->moduleRepository->enabled($moduleName);
    }
}
