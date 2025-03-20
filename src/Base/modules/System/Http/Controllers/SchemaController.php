<?php

namespace Xditn\Base\modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Xditn\Base\modules\Permissions\Models\Roles;
use Xditn\Base\modules\System\Models\RoleHasColumns;
use Xditn\Base\modules\System\Models\TableColumn;
use Xditn\Base\XditnController as Controller;

class SchemaController extends Controller
{
    public function index(Request $request, TableColumn $tableColumn)
    {
        $database = config('database.connections.mysql.database');

        $SQL = <<<SQL
SELECT table_name, engine, table_rows, data_length, index_length, table_comment, table_collation, create_time as created_at FROM information_schema.tables where TABLE_SCHEMA = "{$database}"
SQL;

        $tables = Collection::make(json_decode(json_encode(DB::select($SQL)), true));

        $tables = $tables->map(function ($table) {
            $table = array_change_key_case($table);
            $table['data_length'] = Number::fileSize($table['data_length']);
            $table['index_length'] = Number::fileSize($table['index_length']);

            return $table;
        });

        // 搜索
        if ($request->get('name')) {
            $tables = $tables->filter(function ($table) use ($request) {
                return str_contains($table['table_name'], $request->get('name'));
            })->values();
        }

        $page = $request->get('page');
        $limit = $request->get('limit');

        // 判断表的字段是否跟角色有关联，如果有，则展示相关字段管理
        $hasRoleTables = $tableColumn->getHasRoleTableNames();
        $filterTables = $tables->slice(($page - 1) * $limit, $limit)
                         ->values()
                         ->map(function ($table) use ($hasRoleTables) {
                             $table['has_role_columns'] = in_array($table['table_name'], $hasRoleTables);

                             return $table;
                         });

        return new LengthAwarePaginator(
            $filterTables,
            count($tables),
            $limit,
            $page
        );
    }

    /**
     * 表字段
     *
     * @param $table
     * @param  TableColumn  $tableColumn
     * @return array
     */
    public function fields($table, TableColumn $tableColumn)
    {
        $columns = Schema::getColumns(Str::of($table)->remove(config('database.connections.mysql.prefix'))->toString());
        $tableColumns = $tableColumn->getColumnsContainRoles($table);

        foreach ($columns as &$column) {
            if (isset($tableColumns[$column['name']])) {
                if (! empty($tableColumns[$column['name']]['readable_roles'])) {
                    $column['readable_roles'] = array_column($tableColumns[$column['name']]['readable_roles'], 'id');
                } else {
                    $column['readable_roles'] = [];
                }

                if (! empty($tableColumns[$column['name']]['writeable_roles'])) {
                    $column['writeable_roles'] = array_column($tableColumns[$column['name']]['writeable_roles'], 'id');
                } else {
                    $column['writeable_roles'] = [];
                }

            } else {
                $column['readable_roles'] = [];
                $column['writeable_roles'] = [];
            }
        }

        return $columns;
    }

    /**
     * @param  Request  $request
     * @param  TableColumn  $tableColumn
     * @return mixed
     */
    public function fieldsRoleVisible(Request $request, TableColumn $tableColumn): mixed
    {
        if (DB::transaction(function () use ($request, $tableColumn) {
            // 处理
            $table = $request->get('table');
            $columns = $request->get('columns');

            $saveColumns = [];
            foreach ($columns as $column) {
                if (! empty($column['readable_roles'])) {
                    $saveColumns[] = $column['name'];
                }
                if (! empty($column['writeable_roles'])) {
                    $saveColumns[] = $column['name'];
                }
            }
            // 去重
            $saveColumns = array_unique($saveColumns);
            // 保存 ['column_name' => 'id']
            $savedColumnIds = $tableColumn->storeColumns($table, $saveColumns);

            // 处理可读角色的字段权限
            $dealReadableRoles = function ($columns) use ($savedColumnIds, $table, $tableColumn) {
                // 可读角色 [role_id => [column_id, column_id, ...]]
                $readableRoleHasColumns = [];
                foreach ($columns as $column) {
                    if (! empty($column['readable_roles'])) {
                        foreach ($column['readable_roles'] as $roleId) {
                            $readableRoleHasColumns[$roleId][] = $savedColumnIds[$column['name']];
                        }
                    }
                }

                foreach ($readableRoleHasColumns as $_roleId => $_columnIds) {
                    $role = new Roles();
                    $role->saveReadableColumns($_roleId, $_columnIds, array_values($savedColumnIds));
                }

                // 获取已关联的可读角色ID
                $releatedReadableRoleIds = RoleHasColumns::getReadableRolesByColumnIds($tableColumn->where('table_name', $table)->pluck('id'));
                $roleIds = array_keys($readableRoleHasColumns);

                // 如果没有关联的角色，则删除所有关联的可读角色
                if (! count($readableRoleHasColumns)) {
                    foreach ($releatedReadableRoleIds as $roleId) {
                        $role = new Roles();
                        $role->detachReadableColumns($roleId, $savedColumnIds);
                    }
                } else {
                    foreach ($releatedReadableRoleIds as $roleId) {
                        if (! in_array($roleId, $roleIds)) {
                            //移除可读column的数据
                            $role = new Roles();
                            $role->detachReadableColumns($roleId, $savedColumnIds);
                        }
                    }
                }
            };

            // 处理可写角色的字段权限
            $dealWriteableRoles = function ($columns) use ($savedColumnIds, $table, $tableColumn) {
                // 可写角色
                $writeableRoleHasColumns = [];
                foreach ($columns as $column) {
                    if (! empty($column['writeable_roles'])) {
                        foreach ($column['writeable_roles'] as $roleId) {
                            $writeableRoleHasColumns[$roleId][] = $savedColumnIds[$column['name']];
                        }
                    }
                }

                foreach ($writeableRoleHasColumns as $_roleId => $_columnIds) {
                    $role = new Roles();
                    $role->saveWriteableColumns($_roleId, $_columnIds, array_values($savedColumnIds));
                }

                // 获取已关联的可写角色ID
                $releatedWriteableRoleIds = RoleHasColumns::getWriteableRolesByColumnIds($tableColumn->where('table_name', $table)->pluck('id'));
                $roleIds = array_keys($writeableRoleHasColumns);

                // 如果没有关联的角色，则删除所有关联的可读角色
                if (! count($writeableRoleHasColumns)) {
                    foreach ($releatedWriteableRoleIds as $roleId) {
                        $role = new Roles();
                        $role->detachWriteableColumns($roleId, $savedColumnIds);
                    }
                } else {
                    foreach ($releatedWriteableRoleIds as $roleId) {
                        if (! in_array($roleId, $roleIds)) {
                            //移除可读column的数据
                            $role = new Roles();
                            $role->detachWriteableColumns($roleId, $savedColumnIds);
                        }
                    }
                }
            };

            $dealReadableRoles($columns);
            $dealWriteableRoles($columns);

            return true;
        })) {
            $tableColumns = $tableColumn->with(['readableRoles', 'writeableRoles'])->get();

            $columnHasRoles = [];

            foreach ($tableColumns as $column) {
                if (! $column->readableRoles->isEmpty()) {
                    $columnHasRoles[$column->table_name][$column->column_name]['readable_roles'] =
                            array_unique(array_merge(
                                $columnHasRoles[$column->table_name][$column->column_name]['readable_roles'] ?? [],
                                $column->readableRoles->pluck('id')->toArray()
                            )
                        );
                }

                if (! $column->writeableRoles->isEmpty()) {
                    $columnHasRoles[$column->table_name][$column->column_name]['writeable_roles'] =
                        array_unique(array_merge(
                            $columnHasRoles[$column->table_name][$column->column_name]['writeable_roles'] ?? [],
                            $column->writeableRoles->pluck('id')->toArray()
                        ));
                }
            }

            // 缓存
            Cache::forever('column_has_roles', $columnHasRoles);
        }

        return true;
    }

    /**
     * 获取已有权限的字段
     *
     * @param  Request  $request
     * @param  TableColumn  $tableColumn
     * @return \Illuminate\Database\Eloquent\Collection|Collection|TableColumn[]
     */
    public function fieldsManage(Request $request, TableColumn $tableColumn)
    {
        return $tableColumn->getTableColumns($request->get('table'));
    }

    /**
     * 删除字段
     *
     * @param $id
     * @param  TableColumn  $tableColumn
     * @return bool|null
     */
    public function destroyField($id, TableColumn $tableColumn)
    {
        return $tableColumn->deleteById($id);
    }
}
