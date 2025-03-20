<?php

declare(strict_types=1);

namespace Xditn\Traits\DB;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * WithRelations trait 提供 Eloquent 模型的关联关系处理方法
 */
trait WithRelations
{
    /**
     * 处理模型的关联关系数据（创建时）
     *
     * 该方法在创建模型时处理关联关系的数据。根据数据中的关联关系字段，
     * 将其附加到模型中。如果是 BelongsToMany 关系，执行 attach 操作；
     * 如果是 HasMany 或 HasOne 关系，则创建新的关联数据。
     *
     * @param  array  $data 包含关联关系数据的数组
     */
    protected function createRelations(array $data): void
    {
        foreach ($this->getRelationsData($data) as $relation => $relationData) {
            $isRelation = $this->{$relation}();
            if (! count($relationData)) {
                continue;
            }
            // 处理 BelongsToMany 关系
            if ($isRelation instanceof BelongsToMany) {
                $isRelation->attach($relationData);
            }
            // 处理 HasMany 或 HasOne 关系
            if ($isRelation instanceof HasMany || $isRelation instanceof HasOne) {
                $isRelation->create($relationData);
            }
        }
    }

    /**
     * 处理模型的关联关系数据（更新时）
     *
     * 在更新模型时，处理 BelongsToMany 关系的同步。其他关联关系可以根据需要扩展。
     *
     * @param  Model  $model 要更新的模型
     * @param  array  $data  更新的关联关系数据
     */
    public function updateRelations(Model $model, array $data): void
    {
        foreach ($this->getRelationsData($data) as $relation => $relationData) {
            $isRelation = $model->{$relation}();
            // 处理 BelongsToMany 关系同步
            if ($isRelation instanceof BelongsToMany) {
                $isRelation->sync($relationData);
            }
        }
    }

    /**
     * 删除模型的关联关系
     *
     * 在删除模型时，处理关联关系的删除操作，特别是 BelongsToMany 关系。
     *
     * @param  Model  $model 要删除的模型
     */
    public function deleteRelations(Model $model): void
    {
        $relations = $this->getRelations();
        foreach ($relations as $relation) {
            $isRelation = $model->{$relation}();
            // 处理 BelongsToMany 关系的解除关联
            if ($isRelation instanceof BelongsToMany) {
                $isRelation->detach();
            }
        }
    }

    /**
     * 获取关联关系数据
     *
     * 该方法根据给定的数据获取关联关系数据数组。如果数据中包含与表单定义的关联关系，
     * 则返回这些关联关系的数据。
     *
     * @param  array  $data 提交的数据
     * @return array 关联关系数据
     */
    protected function getRelationsData(array $data): array
    {
        // 获取定义在表单中的关联关系
        $relations = $this->getFormRelations();
        if (empty($relations)) {
            return [];
        }
        $relationsData = [];
        // 筛选出存在于数据中的关联关系
        foreach ($relations as $relation) {
            if (! isset($data[$relation]) || ! $this->isRelation($relation)) {
                continue;
            }
            $relationData = $data[$relation];
            $relationsData[$relation] = $relationData;
        }

        return $relationsData;
    }
}
