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
                $userModel->getTable().'.username as creator',
                $model->getTable().'.*',
            ])->leftJoin(
                $userModel->getTable(),
                $userModel->getTable().'.id',
                '=',
                $model->getTable().'.'.$model->getCreatorIdColumn()
            );
        }
    }
}
