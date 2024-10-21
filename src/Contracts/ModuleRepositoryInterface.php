<?php

declare(strict_types=1);

namespace Xditn\Contracts;

use Illuminate\Support\Collection;

/**
 * 模块仓库接口
 *
 * 该接口定义了与模块管理相关的操作方法，包括获取模块列表、创建模块、查看模块详情、
 * 更新模块、删除模块、启用/禁用模块以及获取启用状态的模块列表等。
 */
interface ModuleRepositoryInterface
{
    /**
     * 根据搜索条件获取模块列表
     *
     * @param  array  $search 搜索条件
     * @return Collection 模块列表
     */
    public function all(array $search): Collection;

    /**
     * 创建新模块
     *
     * @param  array  $module 模块信息
     * @return bool|int 成功返回 true 或新模块的 ID，失败返回 false
     */
    public function create(array $module): bool|int;

    /**
     * 查看指定模块的详情
     *
     * @param  string  $name 模块名称
     * @return Collection 包含模块详情的集合
     */
    public function show(string $name): Collection;

    /**
     * 更新指定模块的信息
     *
     * @param  string  $name 模块名称
     * @param  array  $module 更新的模块信息
     * @return bool|int 成功返回 true 或受影响的行数，失败返回 false
     */
    public function update(string $name, array $module): bool|int;

    /**
     * 删除指定模块
     *
     * @param  string  $name 模块名称
     * @return bool|int 成功返回 true 或受影响的行数，失败返回 false
     */
    public function delete(string $name): bool|int;

    /**
     * 启用或禁用指定模块
     *
     * @param  string  $name 模块名称
     * @return bool|int 成功返回 true 或受影响的行数，失败返回 false
     */
    public function disOrEnable(string $name): bool|int;

    /**
     * 获取所有启用状态的模块列表
     *
     * @return Collection 启用状态的模块列表
     */
    public function getEnabled(): Collection;

    /**
     * 检查指定模块是否启用
     *
     * @param  string  $moduleName 模块名称
     * @return bool 模块启用返回 true，否则返回 false
     */
    public function enabled(string $moduleName): bool;
}
