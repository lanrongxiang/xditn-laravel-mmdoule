<?php

namespace Xditn\Base\modules\System\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;

class RoleHasColumns extends Pivot
{
    protected $table = 'system_role_has_columns';

    protected $fillable = [
        'id', 'role_id', 'table_column_id', 'type',
    ];

    const READABLE = 1;

    const WRITABLE = 2;

    /**
     * 获取可写角色
     *
     * @param  array|Collection  $columnIds
     * @return array
     */
    public static function getWriteableRolesByColumnIds(array|Collection $columnIds): array
    {
        return static::query()->whereIn('table_column_id', $columnIds)->where('type', self::WRITABLE)->pluck('role_id')
                ->unique()
                ->toArray();
    }

    /**
     * 获取可读角色
     *
     * @param  array|Collection  $columnIds
     * @return array
     */
    public static function getReadableRolesByColumnIds(array|Collection $columnIds): array
    {
        return static::query()->whereIn('table_column_id', $columnIds)->where('type', self::READABLE)->pluck('role_id')
            ->unique()
            ->toArray();
    }
}
