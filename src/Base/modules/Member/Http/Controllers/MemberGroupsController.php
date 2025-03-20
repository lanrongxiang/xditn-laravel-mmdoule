<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Member\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\Member\Models\MemberGroups;
use Xditn\Base\XditnController as Controller;

/**
 * @group 会员管理
 *
 * @subgroup 会员组管理
 * @subgroupDescription MModule 后台会员管理 -> 会员组管理
 */
class MemberGroupsController extends Controller
{
    public function __construct(
        protected readonly MemberGroups $model
    ) {
    }

    /**
     * 会员组列表
     *
     * @queryParam name string 会员组名称
     *
     * @responseField code int 状态码
     * @responseField message string 提示信息
     * @responseField limit int 每页显示条数
     * @responseField page int 当前页码
     * @responseField total int 总数
     * @responseField data object[] data
     * @responseField data[].id int 会员组ID
     * @responseField data[].name string 会员组名称
     * @responseField data[].description string 会员组描述
     * @responseField data[].status int 状态
     * @responseField data[].created_at string 创建时间
     *
     * @return mixed
     */
    public function index(): mixed
    {
        return $this->model->getList();
    }

    /**
     * 新增会员组
     *
     * @bodyParam name string 会员组名称
     * @bodyParam description string 会员组描述
     * @bodyParam status int 状态
     *
     * @responseField data bool 是否成功
     *
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request)
    {
        return $this->model->storeBy($request->all());
    }

    /**
     * 更新会员组
     *
     * @urlParam id int required 会员组ID
     *
     * @responseField data object 会员组
     * @responseField data.id int 会员组ID
     * @responseField data.name string 会员组名称
     * @responseField data.description string 会员组描述
     * @responseField data.status int 状态
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
     * 更新会员组
     *
     * @urlParam id int required 会员组ID
     *
     * @bodyParam name string 会员组名称
     * @bodyParam description string 会员组描述
     * @bodyParam status int 状态
     *
     * @responseField data bool 是否成功
     *
     * @return mixed
     */
    public function update($id, Request $request)
    {
        return $this->model->updateBy($id, $request->all());
    }

    /**
     * 删除会员组
     *
     * @urlParam id int required 会员组ID
     *
     * @param $id
     * @return bool|null
     */
    public function destroy($id)
    {
        return $this->model->deleteBy($id);
    }

    /**
     * 启用会员组
     *
     * @urlParam id int required 会员组ID
     *
     * @return bool
     */
    public function enable($id)
    {
        return $this->model->toggleBy($id);
    }
}
