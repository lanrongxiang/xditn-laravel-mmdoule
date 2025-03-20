<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Member\Http\Controllers;

use Xditn\Base\modules\Member\Http\Requests\MemberRequest as Request;
use Xditn\Base\modules\Member\Models\Members;
use Xditn\Base\XditnController as Controller;

/**
 * @group 会员管理
 *
 * @subgroup 会员管理
 * @subgroupDescription MModule 后台会员管理
 */
class MembersController extends Controller
{
    public function __construct(
        protected readonly Members $model
    ) {
    }

    /**
     * 会员列表
     *
     * @queryParam email string 会员邮箱
     * @queryParam mobile string 会员手机号
     * @queryParam username string 会员昵称
     *
     * @responseField code int 状态码
     * @responseField message string 提示信息
     * @responseField limit int 每页显示条数
     * @responseField page int 当前页码
     * @responseField total int 总数
     * @responseField data object[] data
     * @responseField data[].id int 会员ID
     * @responseField data[].username string 会员昵称
     * @responseField data[].email string 会员邮箱
     * @responseField data[].mobile string 会员手机号
     * @responseField data[].status int 状态
     * @responseField data[].avatar string 头像
     * @responseField data[].from string 注册来源:pc=pc,app=app,miniprogram=小程序,admin=后台添加
     * @responseField data[].last_login_at string 最后登录
     * @responseField data[].created_at string 创建时间
     * @responseField data[].updated_at string 更新时间
     *
     * @return mixed
     */
    public function index(): mixed
    {
        return $this->model->getList();
    }

    /**
     * 新增会员
     *
     * @bodyParam username string required 会员昵称
     * @bodyParam email string required 会员邮箱
     * @bodyParam mobile string required 会员手机号
     * @bodyParam password string required 会员密码
     * @bodyParam avatar string 头像
     * @bodyParam from string 注册来源:pc=pc,app=app,miniprogram=小程序,admin=后台添加
     *
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $params = $request->all();
        $params['from'] = $params['from'] ?? 'admin'; // 后台添加

        return $this->model->storeBy($params);
    }

    /**
     * 会员详情
     *
     * @urlParam id int required 会员ID
     *
     * @responseField data object 会员
     * @responseField data.id int 会员ID
     * @responseField data.username string 会员昵称
     * @responseField data.email string 会员邮箱
     * @responseField data.mobile string 会员手机号
     * @responseField data.status int 状态
     * @responseField data.avatar string 头像
     * @responseField data.from string 注册来源:pc=pc,app=app,miniprogram=小程序,admin=后台添加
     *
     * @param $id
     * @return mixed
     */
    public function show($id): mixed
    {
        return $this->model->firstBy($id)->makeHidden('password');
    }

    /**
     *  更新会员
     *
     * @urlParam id int required 会员ID
     *
     * @bodyParam username string required 会员昵称
     * @bodyParam email string required 会员邮箱
     * @bodyParam mobile string required 会员手机号
     * @bodyParam password string 会员密码
     * @bodyParam avatar string 头像
     * @bodyParam from string 注册来源:pc=pc,app=app,miniprogram=小程序,admin=后台添加
     *
     * @param $id
     * @param  Request  $request
     * @return mixed
     */
    public function update($id, Request $request): mixed
    {
        $params = $request->all();
        if (empty($params['password'])) {
            unset($params['password']);
        }

        return $this->model->updateBy($id, $request->all());
    }

    /**
     * 删除会员
     *
     * @urlParam id int required 会员ID
     *
     * @param $id
     * @return mixed
     */
    public function destroy($id): mixed
    {
        return $this->model->deleteBy($id);
    }

    /**
     * 启用会员
     *
     * @urlParam id int required 会员ID
     *
     * @param $id
     * @return bool
     */
    public function enable($id)
    {
        return $this->model->toggleBy($id);
    }
}
