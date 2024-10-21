<?php

declare(strict_types=1);

namespace Xditn\Traits\DB;

use Xditn\Enums\Status;
use Xditn\Exceptions\FailedException;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

/**
 * 基础数据库操作
 */
trait BaseOperate
{
    use WithEvents;
    use WithRelations;

    /**
     * 获取数据列表
     *
     * @return mixed
     */
    public function getList(): mixed
    {
        // 获取字段
        $fields = $this->fields ?? ['*'];

        // 构建查询
        $builder = static::select($fields)->creator()->quickSearch();

        // 数据权限控制
        if ($this->dataRange) {
            $builder->dataRange();
        }

        // 列表获取前的回调
        if ($this->beforeGetList instanceof Closure) {
            $builder = call_user_func($this->beforeGetList, $builder);
        }

        // 排序字段和动态排序
        $sortField = $this->sortField ?: Request::get('sortField');
        if ($sortField && in_array($sortField, $this->getFillable())) {
            $builder = $builder->orderBy($this->aliasField($sortField), $this->sortDesc ? 'desc' : Request::get('order', 'asc'));
        }

        // 默认按主键降序排序
        $builder->orderByDesc($this->aliasField($this->getKeyName()));

        // 返回分页结果或直接获取数据
        return $this->isPaginate
            ? $builder->paginate(Request::get('limit', $this->perPage))
            : ($this->asTree ? $builder->get()->toTree() : $builder->get());
    }

    /**
     * 保存数据
     *
     * @param array $data
     * @return mixed
     */
    public function storeBy(array $data): mixed
    {
        return $this->fill($this->filterData($data))->save() ? $this->getKey() : false;
    }

    /**
     * 创建新记录
     *
     * @param array $data
     * @return mixed
     */
    public function createBy(array $data): mixed
    {
        $model = $this->newInstance();
        return $model->fill($this->filterData($data))->save() ? $model->getKey() : false;
    }

    /**
     * 更新记录
     *
     * @param $id
     * @param array $data
     *
     * @return bool|null
     */
    public function updateBy($id, array $data): ?bool
    {
        $model = $this->find($id);
        return $model->fill($this->filterData($data))->save() ?? $this->updateRelations($model, $data);
    }

    /**
     * 过滤数据：移除 null 和空字符串
     *
     * @param array $data
     * @return array
     */
    protected function filterData(array $data): array
    {
        $fillable = array_unique(array_merge($this->getFillable(), $this->getForm()));

        return array_filter($data, function ($val, $key) use ($fillable) {
            return in_array($key, $fillable) && ($val !== null || $this->autoNull2EmptyString);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * 通过 ID 获取首条记录
     *
     * @param $value
     * @param null $field
     * @param array $columns
     * @return ?Model
     */
    public function firstBy($value, $field = null, array $columns = ['*']): ?Model
    {
        $field = $field ?: $this->getKeyName();
        $model = static::where($field, $value)->first($columns);
        return $this->afterFirstBy ? call_user_func($this->afterFirstBy, $model) : $model;
    }

    /**
     * 删除记录
     *
     * @param $id
     * @param bool $force
     * @return bool|null
     */
    public function deleteBy($id, bool $force = false): ?bool
    {
        $model = static::find($id);

        // 检查是否有子级未删除
        if ($this->hasChildren($model->id)) {
            throw new FailedException('请先删除子级');
        }

        return $force ? $model->forceDelete() : $model->delete();
    }

    /**
     * 检查是否有子级记录
     *
     * @param int $id
     * @return bool
     */
    protected function hasChildren(int $id): bool
    {
        return $this->where($this->getParentIdColumn(), $id)->exists();
    }

    /**
     * 批量删除
     *
     * @param array|string $ids
     * @param bool $force
     * @param Closure|null $callback
     * @return bool
     */
    public function deletesBy(array|string $ids, bool $force = false, Closure $callback = null): bool
    {
        $ids = is_string($ids) ? explode(',', $ids) : $ids;

        DB::transaction(function () use ($ids, $force, $callback) {
            foreach ($ids as $id) {
                $this->deleteBy($id, $force);
            }

            if ($callback) {
                $callback($ids);
            }
        });

        return true;
    }

    /**
     * 启用或禁用状态
     *
     * @param $id
     * @param string $field
     * @return bool
     */
    public function toggleBy($id, string $field = 'status'): bool
    {
        $model = $this->firstBy($id);
        $status = $model->getAttribute($field) == Status::Enable->value() ? Status::Disable->value() : Status::Enable->value();
        $model->setAttribute($field, $status);
        $model->save();

        // 更新子级
        if (in_array($this->getParentIdColumn(), $this->getFillable())) {
            $this->updateChildren($id, $field, $status);
        }
        return true;
    }

    /**
     * 批量启用或禁用
     *
     * @param array|string $ids
     * @param string $field
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
     * 递归更新子级记录
     *
     * @param int|array $parentId
     * @param string $field
     * @param mixed $value
     */
    public function updateChildren(mixed $parentId, string $field, mixed $value): void
    {
        $childrenId = $this->whereIn($this->getParentIdColumn(), (array) $parentId)->pluck('id');
        if ($childrenId->isNotEmpty()) {
            $this->whereIn($this->getParentIdColumn(), $childrenId)->update([$field => $value]);
            $this->updateChildren($childrenId->toArray(), $field, $value);
        }
    }

    /**
     * 给字段添加表别名
     *
     * @param string|array $fields
     * @return string|array
     */
    public function aliasField(string|array $fields): string|array
    {
        return is_string($fields)
            ? sprintf('%s.%s', $this->getTable(), $fields)
            : array_map(fn($field) => sprintf('%s.%s', $this->getTable(), $field), $fields);
    }

    /**
     * 获取更新时间字段
     *
     * @return string|null
     */
    public function getUpdatedAtColumn(): ?string
    {
        return in_array(parent::getUpdatedAtColumn(), $this->getFillable()) ? parent::getUpdatedAtColumn() : null;
    }

    /**
     * 获取创建时间字段
     *
     * @return string|null
     */
    public function getCreatedAtColumn(): ?string
    {
        return in_array(parent::getCreatedAtColumn(), $this->getFillable()) ? parent::getCreatedAtColumn() : null;
    }

    /**
     * 获取创建者 ID 字段
     *
     * @return string
     */
    public function getCreatorIdColumn(): string
    {
        return 'creator_id';
    }

    /**
     * 设置创建者 ID
     *
     * @return $this
     */
    protected function setCreatorId(): static
    {
        $this->setAttribute($this->getCreatorIdColumn(), Auth::guard(getGuardName())->id());
        return $this;
    }

    /**
     * 设置父级 ID 字段
     *
     * @param string $parentId
     * @return $this
     */
    public function setParentIdColumn(string $parentId): static
    {
        $this->parentIdColumn = $parentId;
        return $this;
    }

    /**
     * 设置排序字段
     *
     * @param string $sortField
     * @return $this
     */
    protected function setSortField(string $sortField): static
    {
        $this->sortField = $sortField;
        return $this;
    }

    /**
     * 设置分页
     *
     * @param bool $isPaginate
     * @return $this
     */
    protected function setPaginate(bool $isPaginate = true): static
    {
        $this->isPaginate = $isPaginate;
        return $this;
    }

    /**
     * 清除表单数据
     *
     * @return $this
     */
    public function withoutForm(): static
    {
        if (property_exists($this, 'form')) {
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
     * 获取父级 ID 字段
     *
     * @return string
     */
    public function getParentIdColumn(): string
    {
        return $this->parentIdColumn;
    }

    /**
     * 获取表单关联关系
     *
     * @return array
     */
    public function getFormRelations(): array
    {
        return property_exists($this, 'formRelations') ? $this->formRelations : [];
    }

    /**
     * 设置数据权限范围
     *
     * @param bool $use
     * @return $this
     */
    public function setDataRange(bool $use = true): static
    {
        $this->dataRange = $use;
        return $this;
    }

    /**
     * 设置自动将 null 转换为空字符串
     *
     * @param bool $auto
     * @return $this
     */
    public function setAutoNull2EmptyString(bool $auto = true): static
    {
        $this->autoNull2EmptyString = $auto;
        return $this;
    }

    /**
     * 是否填充创建者 ID
     *
     * @param bool $is
     * @return $this
     */
    public function fillCreatorId(bool $is = true): static
    {
        $this->isFillCreatorId = $is;
        return $this;
    }
}
