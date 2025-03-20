<?php

namespace Xditn\Traits\DB;

trait ScopeTrait
{
    /**
     * 作用域：根据创建者筛选记录
     *
     * @param $query
     * @return void
     */
    public static function scopeCreator($query): void
    {
        $model = app(static::class);
        if (in_array($model->getCreatorIdColumn(), $model->getFillable())) {
            $userModel = app(getAuthUserModel());
            $query->addSelect([
                'creator' => $userModel->whereColumn(
                    $userModel->getKeyName(),
                    $model->getTable().'.'.$model->getCreatorIdColumn()
                )->select('username')->limit(1),
            ]);
        }
    }
}
