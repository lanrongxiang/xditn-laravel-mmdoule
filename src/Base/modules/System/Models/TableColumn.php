<?php

namespace Xditn\Base\modules\System\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Xditn\Base\modules\Permissions\Models\Roles;
use Xditn\Base\XditnModel as Model;

class TableColumn extends Model
{
    protected $table = 'system_table_columns';

    protected $fillable = [
        'id', 'table_name', 'column_name', 'updated_at', 'created_at', 'deleted_at',
    ];

    /**
     *  对应的可读的角色
     *
     * @return BelongsToMany
     */
    public function readableRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            Roles::class,
            RoleHasColumns::class,
            'table_column_id',
            'role_id',
        )->wherePivot('type', RoleHasColumns::READABLE); // 可读类型
    }

    /**
     *  对应的可写的角色
     *
     * @return BelongsToMany
     */
    public function writeableRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            Roles::class,
            RoleHasColumns::class,
            'table_column_id',
            'role_id',
        )->wherePivot('type', RoleHasColumns::WRITABLE); // 可写类型
    }

    /**
     * 保存字段，并且返回已保存的 column ids [column_name => column_id]
     *
     * @param  string  $table
     * @param  array  $columns
     * @return array
     */
    public function storeColumns(string $table, array $columns): array
    {
         $tableColumns = $this->where('table_name', $table)->select(['id', 'column_name'])->get()->keyBy('column_name');

         foreach ($columns as $column) {
             if (! isset($tableColumns[$column])) {
                 $this->createBy([
                     'table_name' => $table,
                     'column_name' => $column,
                 ]);
             }
         }

        $columnIds = [];
        $this->where('table_name', $table)->select(['id', 'column_name'])->get()->each(function ($column) use (&$columnIds) {
            $columnIds[$column['column_name']] = $column['id'];
        });

         return $columnIds;
    }

    /**
     * @param  string  $table
     * @return array
     */
    public function getColumnsContainRoles(string $table): array
    {
        return $this->where('table_name', $table)->with([
            'readableRoles' => function ($query) {
                $query->select('roles.id');
            }, 'writeableRoles' => function ($query) {
                $query->select('roles.id');
            },
        ])->get()->keyBy('column_name')->toArray();
    }

    /**
     * @param  string  $table
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|TableColumn[]
     */
    public function getTableColumns(string $table)
    {
        return $this->where('table_name', $table)->get();
    }

    /**
     * 获取所有拥有角色的表
     *
     * @return array
     */
    public function getHasRoleTableNames(): array
    {
        return $this->whereIn('id', RoleHasColumns::pluck('table_column_id'))->pluck('table_name')->toArray();
    }

    /**
     * @param $id
     * @return bool|mixed|null
     */
    public function deleteById($id): mixed
    {
        return DB::transaction(function () use ($id) {
            $column = $this->find($id);

            parent::deleteBy($id);

            $column->readableRoles()->detach();

            $column->writeableRoles()->detach();

            return true;
        });
    }
}
