<?php

declare(strict_types=1);

namespace Xditn\Traits\DB;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Xditn\Enums\Status;
use Xditn\Exceptions\FailedException;
use Xditn\Facade\Admin;

/**
 * 基础数据库操作 Trait
 *
 * 提供模型的增删改查、批量操作及数据状态切换的通用方法。
 */
trait BaseOperate
{
    use WithEvents;
    use WithRelations;
    use WithSearch;

    /**
     * 获取列表数据
     *
     * 返回类型说明：
     * - LengthAwarePaginator: 启用分页时（$this->isPaginate = true）
     * - Collection: 禁用分页时（$this->isPaginate = false）
     * - Collection: 树形结构时（$this->asTree = true）
     *
     * @return LengthAwarePaginator|Collection
     */
    public function getList(): LengthAwarePaginator|Collection
    {
        $fields = property_exists($this, 'fields') ? $this->fields : ['*'];
        //字段访问,获取可读字段
        if ($this->columnAccess && method_exists($this, 'readable')) {
            $fields = $this->readable($fields);
        }

        $builder = static::select($fields)->creator()->quickSearch();
        //数据权限
        if ($this->dataRange) {
            $builder->dataRange();
        }
        // 自定义查询前回调
        if ($this->beforeGetList instanceof Closure) {
            $builder = call_user_func($this->beforeGetList, $builder);
        }
        //默认排序
        if ($this->sortField && in_array($this->sortField, $this->getFillable())) {
            $builder->orderBy($this->aliasField($this->sortField), $this->getDefaultSortOrder());
        }
        //动态排序
        $dynamicSortField = Request::get($this->dynamicQuerySortField);
        if ($dynamicSortField && $dynamicSortField != $this->sortField) {
            $builder->orderBy($this->aliasField($dynamicSortField), Request::get($this->dynamicQuerySortOrder, 'asc'));
        }
        $builder->orderByDesc($this->aliasField($this->getKeyName()));
        //分页
        $limit = Request::get('limit', $this->perPage);

        // 如果设置 asTree 属性为 true，将会返回树形结构
        return $this->isUseTrashed()
            ? $builder->onlyTrashed()->paginate($limit)
            : ($this->isPaginate
                ? $builder->paginate($limit)
                : $builder->get()->when($this->asTree, fn ($collection) => $collection->toTree()));
    }

    /**
     * 保存数据（更新或创建）
     *
     *   Laravel 的 save() 方法，但会自动处理关联关系
     * 注意：与 Laravel 原生 save() 的区别：
     * - Laravel save() 返回 boolean
     * - 本方法返回 Model 实例（符合链式调用习惯）
     *
     * @param  array  $data
     * @return ?Model
     *
     * @throws FailedException
     */
    public function storeBy(array $data): ?Model
    {
        if (! $this->fill($this->filterData($data))->save()) {
            throw new FailedException('数据保存失败');
        }

        // 处理关联关系
        if ($this->getKey()) {
            $this->createRelations($data);
        }

        return $this;
    }

    /**
     * 创建新数据
     *
     *  Laravel 的 create() 方法
     * Laravel create() 返回 Model 实例，本方法保持一致
     *
     * @param  array  $data
     * @return ?Model
     *
     * @throws FailedException
     */
    public function createBy(array $data): ?Model
    {
        $model = $this->newInstance();

        if (! $model->fill($this->filterData($data))->save()) {
            throw new FailedException('数据创建失败');
        }

        // 处理关联关系
        if ($model->getKey()) {
            $model->createRelations($data);
        }

        return $model;
    }

    /**
     * 更新数据
     *
     * @param  mixed  $id
     * @param  array  $data
     * @return bool
     *
     * @throws FailedException
     */
    public function updateBy(mixed $id, array $data): bool
    {
        $model = $this->where($this->getKeyName(), $id)->first();

        if (! $model) {
            throw new FailedException('数据不存在，无法更新');
        }

        $updated = $model->fill($this->filterData($data, true))->save();

        if ($updated) {
            $this->updateRelations($this->find($id), $data);
        }

        return $updated;
    }

    /**
     * 批量更新数据
     *
     * 使用 Laravel Query Builder 替代原生 SQL，防止 SQL 注入
     *
     * @param  string  $field 更新条件字段
     * @param  array  $condition 条件值数组
     * @param  array  $data 要更新的数据 ['field' => [values]]
     * @return bool
     *
     * @throws FailedException
     */
    public function batchUpdate(string $field, array $condition, array $data): bool
    {
        if (empty($condition) || empty($data)) {
            throw new FailedException('批量更新参数不能为空');
        }

        try {
            DB::transaction(function () use ($field, $condition, $data) {
                foreach ($condition as $index => $conditionValue) {
                    $updateData = [];

                    // 组装当前记录的更新数据
                    foreach ($data as $key => $values) {
                        if (isset($values[$index])) {
                            $updateData[$key] = $values[$index];
                        }
                    }

                    if (! empty($updateData)) {
                        // 使用 Query Builder，自动处理参数绑定，防止 SQL 注入
                        $this->where($field, $conditionValue)->update($updateData);
                    }
                }
            });

            return true;
        } catch (\Throwable $exception) {
            throw new FailedException('批量更新错误: '.$exception->getMessage());
        }
    }

    /**
     * 批量更新数据（CASE WHEN 优化版本，性能更好）
     *
     * 仅在数据量大且更新字段少时使用
     * 使用参数绑定防止 SQL 注入
     *
     * @param  string  $field 更新条件字段
     * @param  array  $condition 条件值数组
     * @param  array  $data 要更新的数据 ['field' => [values]]
     * @return bool
     *
     * @throws FailedException
     */
    public function batchUpdateOptimized(string $field, array $condition, array $data): bool
    {
        if (empty($condition) || empty($data)) {
            throw new FailedException('批量更新参数不能为空');
        }

        try {
            $cases = [];
            $bindings = [];
            $ids = array_map('intval', $condition); // 强制转换为整数，增加安全性

            foreach ($data as $key => $values) {
                $caseSql = 'CASE ';

                foreach ($condition as $index => $id) {
                    $caseSql .= "WHEN `{$field}` = ? THEN ? ";
                    $bindings[] = $id;
                    $bindings[] = $values[$index];
                }

                $caseSql .= "ELSE `{$key}` END";
                $cases[] = "`{$key}` = {$caseSql}";
            }

            if (empty($cases)) {
                return true;
            }

            // 添加 WHERE 条件的绑定
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $bindings = array_merge($bindings, $ids);

            $sql = sprintf(
                'UPDATE `%s` SET %s WHERE `%s` IN (%s)',
                $this->getTable(),
                implode(', ', $cases),
                $field,
                $placeholders
            );

            return DB::update($sql, $bindings) > 0;
        } catch (\Throwable $exception) {
            throw new FailedException('批量更新错误: '.$exception->getMessage());
        }
    }

    /**
     * 过滤数据，移除 null 和空字符串
     */
    protected function filterData(array $data, bool $isUpdate = false): array
    {
        $fillable = array_unique(array_merge($this->getFillable(), $this->getForm()));

        foreach ($data as $k => $val) {
            if ($this->autoNull2EmptyString && is_null($val)) {
                $data[$k] = '';
            }
            if (! empty($fillable) && ! in_array($k, $fillable)) {
                unset($data[$k]);
            }
        }

        if ($this->columnAccess && method_exists($this, 'writable')) {
            $keys = $this->writable(array_keys($data));
            $data = array_filter($data, fn ($key) => in_array($key, $keys), ARRAY_FILTER_USE_KEY);
        }

        if (! $this->timestamps) {
            $data = $this->handleTimestamps($data, $isUpdate);
        }

        if ($this->isFillCreatorId && in_array($this->getCreatorIdColumn(), $this->getFillable())) {
            $data[$this->getCreatorIdColumn()] = $data[$this->getCreatorIdColumn()] ?? Admin::id() ?: 0;
        }

        return $data;
    }

    /**
     * 处理时间戳字段
     *
     * @param  array  $data
     * @param  bool  $isUpdate
     * @return array
     */
    protected function handleTimestamps(array $data, bool $isUpdate): array
    {
        if (! $isUpdate && ($createdAtColumn = $this->getCreatedAtColumn())) {
            $data[$createdAtColumn] = time();
        }
        if ($isUpdate && isset($data[$this->getCreatedAtColumn()])) {
            unset($data[$this->getCreatedAtColumn()]);
        }
        if ($updatedAtColumn = $this->getUpdatedAtColumn()) {
            $data[$updatedAtColumn] = time();
        }

        return $data;
    }

    /**
     * 根据ID获取单条数据
     *
     * @param  mixed  $value
     * @param  mixed  $field
     * @param  array  $columns
     * @return ?Model
     */
    public function firstBy(mixed $value, mixed $field = null, array $columns = ['*']): ?Model
    {
        $field = $field ?? $this->getKeyName();
        $columns = ($this->columnAccess && method_exists($this, 'readable')) ? $this->readable($columns) : $columns;
        $model = static::where($field, $value)->first($columns);

        if ($this->afterFirstBy) {
            $model = call_user_func($this->afterFirstBy, $model);
        }

        return $model;
    }

    /**
     * 删除数据
     *
     * @param  mixed  $id
     * @param  bool  $force
     * @param  bool  $softForce
     * @return bool
     *
     * @throws FailedException
     */
    public function deleteBy(mixed $id, bool $force = false, bool $softForce = false): bool
    {
        $model = static::find($id);

        if (! $model) {
            throw new FailedException('数据不存在，无法删除');
        }

        if (in_array($this->getParentIdColumn(), $this->getFillable()) &&
            $this->where($this->getParentIdColumn(), $model->id)->first()) {
            throw new FailedException('请先删除子级');
        }

        $deleted = $force ? $model->forceDelete() : $model->delete();

        if ($deleted && ! $softForce) {
            $this->deleteRelations($model);
        }

        return (bool) $deleted;
    }

    /**
     * 删除软删除数据
     *
     * @param  mixed  $id 数据ID
     * @return bool
     *
     * @throws FailedException
     */
    public function deleteTrash(mixed $id): bool
    {
        $model = static::onlyTrashed()->find($id);

        if (! $model) {
            throw new FailedException('回收站中未找到该数据');
        }

        return (bool) $model->forceDelete();
    }

    /**
     * 批量删除数据
     *
     * @param  array|string  $ids      数据ID集合
     * @param  bool  $force    是否强制删除
     * @param  Closure|null  $callback 删除后的回调函数
     * @return bool
     *
     * @throws \Throwable
     */
    public function deletesBy(array|string $ids, bool $force = false, ?Closure $callback = null): bool
    {
        $ids = is_string($ids) ? explode(',', $ids) : $ids;

        DB::transaction(function () use ($ids, $force, $callback) {
            if ($this->isUseTrashed()) {
                foreach ($ids as $id) {
                    $this->deleteTrash($id);
                }
            } else {
                foreach ($ids as $id) {
                    $this->deleteBy($id, $force);
                }
            }

            if ($callback) {
                $callback($ids);
            }
        });

        return true;
    }

    /**
     * 恢复软删除的数据
     *
     * @param  array|string  $ids 数据ID集合
     * @return bool
     */
    public function restoreBy(array|string $ids): bool
    {
        $ids = is_string($ids) ? explode(',', $ids) : $ids;

        if (count($ids) === 1) {
            $model = $this->onlyTrashed()->find($ids[0]);

            return $model ? $model->restore() : false;
        }

        $count = $this->whereIn($this->getKeyName(), $ids)
             ->onlyTrashed()
             ->update([$this->getDeletedAtColumn() => 0]);

        return $count > 0;
    }

    /**
     * 切换状态（启用或禁用）
     *
     * @param  mixed  $id 数据ID
     * @param  string  $field 状态字段
     * @return bool
     */
    public function toggleBy(mixed $id, string $field = 'status'): bool
    {
        $model = $this->firstBy($id);

        if (! $model) {
            throw new FailedException('数据不存在');
        }

        $newStatus = $model->getAttribute($field) == Status::Enable->value()
            ? Status::Disable->value()
            : Status::Enable->value();

        $model->setAttribute($field, $newStatus);

        if ($model->save() && in_array($this->getParentIdColumn(), $this->getFillable()) && in_array($field, $this->syncParentFields)) {
            $this->updateChildren($id, $field, $newStatus);
        }

        return true;
    }

    /**
     * 批量切换状态
     *
     * @param  array|string  $ids 数据ID集合
     * @param  string  $field 状态字段
     * @return bool
     */
    public function togglesBy(array|string $ids, string $field = 'status'): bool
    {
        $ids = is_string($ids) ? explode(',', $ids) : $ids;

        DB::transaction(function () use ($ids, $field) {
            foreach ($ids as $id) {
                $this->toggleBy($id, $field);
            }
        });

        return true;
    }

    /**
     * 递归更新子级数据
     *
     * @param  mixed  $parentId 父级ID
     * @param  string  $field 更新的字段
     * @param  mixed  $value 字段值
     */
    public function updateChildren(mixed $parentId, string $field, mixed $value): void
    {
        $parentId = $parentId instanceof Arrayable ? $parentId : Collection::make([$parentId]);

        $childrenId = $this->whereIn($this->getParentIdColumn(), $parentId)->pluck('id');

        if ($childrenId->count()) {
            $this->whereIn($this->getParentIdColumn(), $parentId)->update([$field => $value]);
            $this->updateChildren($childrenId, $field, $value);
        }
    }

    /**
     * 获取带表名的字段
     *
     * @param  string|array  $fields 字段名或字段集合
     * @return string|array 带表名的字段
     */
    public function aliasField(string|array $fields): string|array
    {
        $table = $this->getTable();

        if (is_string($fields)) {
            return sprintf('%s.%s', $table, $fields);
        }

        return array_map(fn ($field) => sprintf('%s.%s', $table, $field), $fields);
    }

    /**
     * 获取更新时间字段
     *
     * @return string|null
     */
    public function getUpdatedAtColumn(): ?string
    {
        $column = parent::getUpdatedAtColumn();

        return in_array($column, $this->getFillable()) ? $column : null;
    }

    /**
     * 检查是否启用软删除
     *
     * @return bool
     */
    protected function isUseTrashed(): bool
    {
        return Request::get('trashed') && in_array($this->getDeletedAtColumn(), $this->getFillable());
    }

    /**
     * 获取创建时间字段
     *
     * @return string|null
     */
    public function getCreatedAtColumn(): ?string
    {
        $column = parent::getCreatedAtColumn();

        return in_array($column, $this->getFillable()) ? $column : null;
    }

    /**
     * 获取创建人ID字段
     *
     * @return string
     */
    public function getCreatorIdColumn(): string
    {
        return 'creator_id';
    }

    /**
     * 设置创建人ID
     *
     * @return static
     */
    protected function setCreatorId(): static
    {
        $this->setAttribute($this->getCreatorIdColumn(), Admin::id());

        return $this;
    }

    /**
     * 设置父级ID字段
     *
     * @param  string  $parentId
     * @return static
     */
    public function setParentIdColumn(string $parentId): static
    {
        $this->parentIdColumn = $parentId;

        return $this;
    }

    /**
     * 设置排序字段
     *
     * @param  string  $sortField
     * @return static
     */
    protected function setSortField(string $sortField): static
    {
        $this->sortField = $sortField;

        return $this;
    }

    /**
     * 设置分页开关
     *
     * @param  bool  $isPaginate
     * @return static
     */
    public function setPaginate(bool $isPaginate = true): static
    {
        $this->isPaginate = $isPaginate;

        return $this;
    }

    /**
     * 清空表单数据
     *
     * @return static
     */
    public function withoutForm(): static
    {
        if (property_exists($this, 'form') && ! empty($this->form)) {
            $this->form = [];
        }

        return $this;
    }

    /**
     * 获取表单数据
     *
     * @return array
     */
    public function getForm(): array
    {
        return property_exists($this, 'form') ? $this->form : [];
    }

    /**
     * 获取默认排序顺序
     *
     * @return string
     */
    protected function getDefaultSortOrder(): string
    {
        return property_exists($this, 'sortDesc') && $this->sortDesc ? 'asc' : 'desc';
    }

    /**
     * 获取父级ID字段
     *
     * @return string
     */
    public function getParentIdColumn(): string
    {
        return $this->parentIdColumn;
    }

    /**
     * 获取表单关联数据
     *
     * @return array
     */
    public function getFormRelations(): array
    {
        return property_exists($this, 'formRelations') ? $this->formRelations : [];
    }

    /**
     * 设置数据范围开关
     *
     * @param  bool  $use
     * @return static
     */
    public function setDataRange(bool $use = true): static
    {
        $this->dataRange = $use;

        return $this;
    }

    /**
     * 设置字段访问开关
     *
     * @param  bool  $use
     * @return static
     */
    public function setColumnAccess(bool $use = true): static
    {
        $this->columnAccess = $use;

        return $this;
    }

    /**
     * 设置空值转换为空字符串
     *
     * @param  bool  $auto
     * @return static
     */
    public function setAutoNull2EmptyString(bool $auto = true): static
    {
        $this->autoNull2EmptyString = $auto;

        return $this;
    }

    /**
     * 设置树形结构开关
     *
     * @return static
     */
    public function asTree(): static
    {
        $this->asTree = true;

        return $this;
    }

    /**
     * 禁用分页
     *
     * @return static
     */
    public function disablePaginate(): static
    {
        $this->isPaginate = false;

        return $this;
    }

    /**
     * 填充创建人ID
     *
     * @param  bool  $is
     * @return static
     */
    public function fillCreatorId(bool $is = true): static
    {
        $this->isFillCreatorId = $is;

        return $this;
    }

    /**
     * 设置需要同步的字段
     *
     * @param  array|null  $fields
     * @return $this
     */
    public function setSyncParentFields(?array $fields = []): static
    {
        if (is_null($fields)) {
            $this->syncParentFields = [];
        } else {
            $this->syncParentFields = array_merge($this->syncParentFields, $fields);
        }

        return $this;
    }
}
