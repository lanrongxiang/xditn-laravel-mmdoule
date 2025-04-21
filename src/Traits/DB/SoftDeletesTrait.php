<?php

namespace Xditn\Traits\DB;

use Illuminate\Database\Eloquent\SoftDeletes;
use Xditn\Support\DB\SoftDelete;

trait SoftDeletesTrait
{
    use SoftDeletes;

    /**
     * 覆盖 restore 方法
     *
     * 修改 deleted_at 默认值
     */
    public function restore(): bool
    {
        if ($this->fireModelEvent('restoring') === false) {
            return false;
        }

        $this->{$this->getDeletedAtColumn()} = 0;

        $this->exists = true;

        $result = $this->save();

        $this->fireModelEvent('restored', false);

        return $result;
    }

    /**
     * 启用软删除
     *
     */
    public static function bootSoftDeletes(): void
    {
        static::addGlobalScope(new SoftDelete());
    }
}