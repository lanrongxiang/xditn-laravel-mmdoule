<?php

namespace Xditn\Base\modules\Develop\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\Develop\Models\SchemaFiles;
use Xditn\Base\modules\Develop\Models\Schemas;
use Xditn\Base\modules\Develop\Support\SchemaColumns;
use Xditn\Base\XditnController;

/**
 * @group 开发模块
 *
 * @subgroup 数据库表管理
 * @subgroupDescription MModule 数据库表管理
 */
class SchemaController extends XditnController
{
    public function __construct(
        protected Schemas $schemas
    ) {
    }

    /**
     * 数据表列表
     *
     * @responseField code int 状态码
     * @responseField message string 提示信息
     * @responseField page int 当前页
     * @responseField total int 总数
     * @responseField limit int 每页数量
     * @responseField data object[] 数据
     *
     * @responseField data[].id int 表ID
     * @responseField data[].name string 表名
     * @responseField data[].comment string 表注释
     * @responseField data[].module string 所属模块
     * @responseField data[].columns string 字段,例如:id,nickname,password,email,homepage,active_key,status...
     * @responseField data[].created_at string 创建时间
     * @responseField data[].updated_at string 更新时间
     * @responseField data[].is_soft_delete tinyint 是否是软删除
     *
     * @return mixed
     */
    public function index()
    {
        return $this->schemas->getList();
    }

    /**
     * 新增表
     *
     * @bodyParam schema object required 数据表对象
     * @bodyParam schema.module string required 所属模块
     * @bodyParam schema.name string required 表名
     * @bodyParam schema.comment string 表注释
     * @bodyParam schema.engine string 表引擎:innodb
     * @bodyParam schema.charset string 字符集:utf8mb4
     * @bodyParam schema.collection string 字符集:utf8mb4_unicode_ci
     * @bodyParam schema.created_at boolean 是否创建 created_at 字段
     * @bodyParam schema.updated_at boolean 是否创建 updated_at 字段
     * @bodyParam schema.creator_id string 是否创建 creator_id 字段
     * @bodyParam schema.deleted_at string 是否软删除
     *
     * @bodyParam structures object[] required 字段列表
     * @bodyParam structures[].field string 字段名称
     * @bodyParam structures[].type string 字段类型
     * @bodyParam structures[].length int 字段长度 默认0
     * @bodyParam structures[].nullable boolean 是否是 nullable
     * @bodyParam structures[].unique boolean 是否是唯一索引字段,默认`false`
     * @bodyParam structures[].default string 默认值
     * @bodyParam structures[].comment string 字段注释
     *
     * @param  Request  $request
     * @return bool
     *
     * @throws \Exception
     */
    public function store(Request $request)
    {
        return $this->schemas->storeBy($request->all());
    }

    /**
     * 查询表数据
     *
     * @urlParam id int required 表ID
     *
     * @param $id
     * @param  SchemaColumns  $schemaColumns
     * @return mixed
     */
    public function show($id, SchemaColumns $schemaColumns): mixed
    {
        $schema = $this->schemas->show($id)->toArray();

        $schema['columns'] = $schemaColumns->parse($schema['columns']);

        return $schema;
    }

    /**
     * 删除表
     *
     * @urlParam id int required 表ID
     *
     * @param $id
     * @return bool|null
     */
    public function destroy($id)
    {
        return $this->schemas->deleteBy($id);
    }

    /**
     * 文件列表
     *
     * @urlParam id int required 表ID
     *
     * @responseField controller_file string 控制器文件
     * @responseField model_file string 模型文件
     * @responseField request_file string 请求文件
     * @responseField table_file string 前端列表页文件
     * @responseField form_file string 前端表单文件
     * @responseField controller_path string 控制器文件路径
     * @responseField model_path string 模型文件路径
     * @responseField request_path string 请求文件路径
     * @responseField table_path string 前端列表页文件路径
     * @responseField form_path string 前端表单文件路径
     *
     * @param $id
     * @param  SchemaFiles  $schemaFiles
     * @param  Request  $request
     * @return bool|null
     */
    public function files($id, SchemaFiles $schemaFiles, Request $request)
    {
        if ($request->isMethod('PUT')) {
            return $schemaFiles->where('schema_id', $id)->update($request->all());
        }

        return $schemaFiles->where('schema_id', $id)->first([
            'controller_file', 'model_file', 'request_file', 'table_file', 'form_file',
            'controller_path', 'model_path', 'request_path', 'table_path', 'form_path',
        ]);
    }
}
