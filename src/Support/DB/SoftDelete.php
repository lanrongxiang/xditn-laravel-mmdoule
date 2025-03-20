<?php

namespace Xditn\Support\DB;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * 自定义软删除逻辑
 *
 * 通过覆盖 Laravel 默认的 SoftDeletingScope，提供了更加灵活的软删除数据查询。
 */
class SoftDelete extends SoftDeletingScope
{
    /**
     * 应用全局查询作用域，过滤掉已删除的数据（deleted_at 为 0）。
     *
     * @param  Builder  $builder 查询构造器
     * @param  Model  $model 模型实例
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // 仅查询未被软删除的数据（deleted_at = 0）
        $builder->where($model->getQualifiedDeletedAtColumn(), '=', 0);
    }

    /**
     * 添加 withTrashed 方法，用于查询包含已删除数据的记录。
     *
     * @param  Builder  $builder 查询构造器
     * @return void
     */
    protected function addWithTrashed(Builder $builder): void
    {
        $builder->macro('withTrashed', function (Builder $builder, $withTrashed = true) {
            if (! $withTrashed) {
                return $builder->withoutTrashed();
            }

            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * 添加 withoutTrashed 方法，用于排除已删除的数据。
     *
     * @param  Builder  $builder 查询构造器
     * @return void
     */
    protected function addWithoutTrashed(Builder $builder): void
    {
        $builder->macro('withoutTrashed', function (Builder $builder) {
            $model = $builder->getModel();

            // 排除已删除的数据（deleted_at = 0）
            $builder->withoutGlobalScope($this)
                    ->where($model->getQualifiedDeletedAtColumn(), 0);

            return $builder;
        });
    }

    /**
     * 添加 onlyTrashed 方法，仅查询已删除的数据。
     *
     * @param  Builder  $builder 查询构造器
     * @return void
     */
    protected function addOnlyTrashed(Builder $builder): void
    {
        $builder->macro('onlyTrashed', function (Builder $builder) {
            $model = $builder->getModel();

            // 仅查询已删除的数据（deleted_at > 0）
            $builder->withoutGlobalScope($this)
                    ->where($model->getQualifiedDeletedAtColumn(), '>', 0);

            return $builder;
        });
    }
}
