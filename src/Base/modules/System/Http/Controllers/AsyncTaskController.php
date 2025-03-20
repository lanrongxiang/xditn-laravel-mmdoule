<?php

declare(strict_types=1);

namespace Xditn\Base\modules\System\Http\Controllers;

use Xditn\Base\modules\System\Models\AsyncTask;
use Xditn\Base\XditnController as Controller;

/**
 * @group 系统管理
 *
 * @subgroup 异步任务
 * @subgroupDescription MModule 后台系统管理->异步任务
 */
class AsyncTaskController extends Controller
{
    public function __construct(
        protected readonly AsyncTask $model
    ) {
    }

    /**
     * 任务列表
     *
     * @responseField code int 状态码
     * @responseField message string 提示信息
     * @responseField limit int 每页数量
     * @responseField page int 当前页
     * @responseField total int 总数
     * @responseField data object[] 数据
     * @responseField data[].id int ID
     * @responseField data[].task string 名称
     * @responseField data[].params string 任务参数
     * @responseField data[].start_at string 任务开始时间
     * @responseField data[].status int 任务状态
     * @responseField data[].error string 错误信息
     * @responseField data[].time_taken string 任务执行时间
     * @responseField data[].retry int 重试次数
     * @responseField data[].created_at string 创建时间
     *
     * @return mixed
     */
    public function index(): mixed
    {
        return $this->model->getList();
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
