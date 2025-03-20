<?php

declare(strict_types=1);

namespace Xditn\Base\modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\System\Models\SystemCronTasks;
use Xditn\Base\XditnController as Controller;

/**
 * @group 系统管理
 *
 * @subgroup 定时任务
 * @subgroupDescription MModule 后台系统管理->定时任务
 */
class CronTasksController extends Controller
{
    public function __construct(
        protected readonly SystemCronTasks $model
    ) {
    }

    /**
     * 任务列表
     *
     * @urlParam page int 页码
     * @urlParam limit int 每页数量
     * @urlParam name string 名称
     * @urlParam command string 任务命令
     *
     * @responseField code int 状态码
     * @responseField message string 提示信息
     * @responseField limit int 每页数量
     * @responseField page int 当前页
     * @responseField total int 总数
     * @responseField data object[] 数据
     * @responseField data[].id int ID
     * @responseField data[].name string 名称
     * @responseField data[].command string 任务参数
     * @responseField data[].run_at string 任务开始时间
     * @responseField data[].run_end_at int 任务状态
     * @responseField data[].consuming string 错误信息
     * @responseField data[].success_times int 成功次数
     * @responseField data[].failed_times int 失败次数
     * @responseField data[].status int 任务状态
     * @responseField data[].created_at string 创建时间
     *
     * @return mixed
     */
    public function index(): mixed
    {
        return $this->model->getList();
    }

    /**
     * 新增任务
     *
     * @bodyParam name string required 名称
     * @bodyParam command string required 任务参数
     * @bodyParam cycle string required 任务周期
     * @bodyParam start_at string 任务开始时间
     * @bodyParam end_at string 任务结束时间
     * @bodyParam days int 运行在某天
     * @bodyParam is_on_one_server int 单台服务器运行
     * @bodyParam is_overlapping string required 是否重复运行
     * @bodyParam is_schedule string required 是否调度
     *
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request)
    {
        return $this->model->storeBy($request->all());
    }

    /**
     * 任务详情
     *
     * @queryParam id int required 任务ID
     * @urlParam id int required 任务ID
     *
     * @responseField code int 状态码
     * @responseField message string 提示信息
     * @responseField data object 任务详情
     * @responseField data.id int ID
     * @responseField data.name string 名称
     * @responseField data.command string 任务参数
     * @responseField data.cycle string 任务周期
     * @responseField data.start_at string 任务开始时间
     * @responseField data.end_at string 任务结束时间
     * @responseField data.days int 运行在某天
     * @responseField data.is_on_one_server int 单台服务器运行
     * @responseField data.is_overlapping string 是否重复运行
     * @responseField data.is_schedule string 是否调度
     * @responseField data.created_at string 创建时间
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function show($id)
    {
        return $this->model->firstBy($id);
    }

    /**
     * 更新任务
     *
     * @urlParam id int required 任务ID
     *
     * @bodyParam name string required 名称
     * @bodyParam command string required 任务参数
     * @bodyParam cycle string required 任务周期
     * @bodyParam start_at string 任务开始时间
     * @bodyParam end_at string 任务结束时间
     * @bodyParam days int 运行在某天
     * @bodyParam is_on_one_server int 单台服务器运行
     * @bodyParam is_overlapping string required 是否重复运行
     * @bodyParam is_schedule string required 是否调度
     *
     * @param $id
     * @param  Request  $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
        return $this->model->updateBy($id, $request->all());
    }

    /**
     * 删除任务
     *
     * @urlParam id int required 任务ID
     *
     * @param $id
     * @return bool|null
     */
    public function destroy($id)
    {
        return $this->model->deleteBy($id);
    }
}
