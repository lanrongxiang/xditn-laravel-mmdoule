<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Cms\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Xditn\Base\modules\Cms\Models\Category;
use Xditn\Base\modules\Cms\Models\Post;
use Xditn\Base\XditnController as Controller;

/**
 * @group 内容管理
 *
 * @subgroup 文章管理
 * @subgroupDescription MModule 后台内容管理->文章管理
 */
class PostController extends Controller
{
    public function __construct(
        protected readonly Post $model
    ) {
    }

    /**
     * 文章列表
     *
     * @queryParam title string 文章标题
     * @queryParam category int 文章分类
     *
     * @responseField code int 状态码
     * @responseField message string 提示信息
     * @responseField page int 当前页
     * @responseField total int 总数
     * @responseField limit int 每页数量
     * @responseField data object[] 文章数据
     * @responseField data[].id int 文章ID
     * @responseField data[].title string 文章标题
     * @responseField data[].category string 文章分类
     * @responseField data[].is_can_comment int 是否允许评论:1 可以 2 不可以
     * @responseField data[].top int 是否置顶:1 分类置顶 3 首页置顶 2 全局置顶
     * @responseField data[].order int 排序
     * @responseField data[].status int 文章状态:1 草稿 2 发布
     * @responseField data[].created_at string 创建时间
     * @responseField data[].updated_at string 更新时间
     *
     * @return mixed
     */
    public function index(): mixed
    {
        return $this->model->setBeforeGetList(function ($query) {
            return $query->select($this->model->aliasField('id'), 'title', $this->model->aliasField('status'), 'is_can_comment', 'order', 'top', $this->model->aliasField('updated_at'), 'admin_users.username as creator')
                ->leftJoin('admin_users', 'admin_users.id', '=', $this->model->getTable().'.author')
                ->addSelect([
                    'category' => Category::whereColumn('id', $this->model->getTable().'.category_id')->select(DB::raw('name'))->limit(1),
                ]);
        })->getList();
    }

    /**
     * 新增文章
     *
     * @bodyParam title string required 文章标题
     * @bodyParam category_id int required 文章分类ID
     * @bodyParam content string required 文章内容
     * @bodyParam excerpt string required 文章摘要
     * @bodyParam author int 文章作者ID
     * @bodyParam cover string[] 文章封面
     * @bodyParam visible int 可见性 1 公开 2 私密 3 密码查看
     * @bodyParam password string 密码:当可见性是 3 时生效
     * @bodyParam seo_title string SEO标题
     * @bodyParam seo_keywords string SEO关键字
     * @bodyParam seo_description string SEO描述
     * @bodyParam is_can_comment int 是否允许评论:1 可以 2 不可以
     * @bodyParam top int 是否置顶:1 分类置顶 3 首页置顶 2 全局置顶
     * @bodyParam order int 排序
     * @bodyParam status int 文章状态:1 草稿 2 发布
     * @bodyParam tags string[] 文章标签
     * @bodyParam type 文章类型 1 文章 2 页面
     *
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request)
    {
        return $this->model->storeBy($request->all());
    }

    /**
     * 文章详情
     *
     * @urlParam id required
     *
     * @responseField data object 文章详情
     * @responseField data.id int 文章ID
     * @responseField data.title string 文章标题
     * @responseField data.category string 文章分类
     * @responseField data.content string 文章内容
     * @responseField data.excerpt string 文章摘要
     * @responseField data.author string 文章作者
     * @responseField data.cover string 文章封面
     * @responseField data.is_can_comment int 是否允许评论:1 可以 2 不可以
     * @responseField data.top int 是否置顶:1 分类置顶 3 首页置顶 2 全局置顶
     * @responseField data.order int 排序
     * @responseField data.status int 文章状态:1 草稿 2 发布
     * @responseField data.created_at string 创建时间
     * @responseField data.visible int 可见性 1 公开 2 私密 3 密码查看
     * @responseField data.password string 密码
     * @responseField data.seo_title string SEO标题
     * @responseField data.seo_keywords string SEO关键字
     * @responseField data.seo_description string SEO描述
     * @responseField data.type int 文章类型 1 文章 2 页面
     * @responseField data.tags string[] 文章标签
     *
     * @param $id
     * @return Model|null
     */
    public function show($id): ?Model
    {
        $post = $this->model->firstBy($id);

        $post->tags = $post->tags()->get()->pluck('name');

        return $post;
    }

    /**
     * 更新文章
     *
     * @urlParam id int required
     *
     * @bodyParam title string required 文章标题
     * @bodyParam category_id int required 文章分类ID
     * @bodyParam content string required 文章内容
     * @bodyParam excerpt string required 文章摘要
     * @bodyParam author int 文章作者ID
     * @bodyParam cover string[] 文章封面
     * @bodyParam visible int 可见性 1 公开 2 私密 3 密码查看
     * @bodyParam password string 密码:当可见性是 3 时生效
     * @bodyParam seo_title string SEO标题
     * @bodyParam seo_keywords string SEO关键字
     * @bodyParam seo_description string SEO描述
     * @bodyParam is_can_comment int 是否允许评论:1 可以 2 不可以
     * @bodyParam top int 是否置顶:1 分类置顶 3 首页置顶 2 全局置顶
     * @bodyParam order int 排序
     * @bodyParam status int 文章状态:1 草稿 2 发布
     * @bodyParam tags string[] 文章标签
     * @bodyParam type 文章类型 1 文章 2 页面
     *
     * @param $id
     * @param  Request  $request
     * @return true
     */
    public function update($id, Request $request): true
    {
        if ($this->model->updateBy($id, $request->all())) {
            $this->model->savePostTags($this->model->firstBy($id));
        }

        return true;
    }

    /**
     * 删除文章
     *
     * @urlParam id int required
     *
     * @param $id
     * @return bool
     */
    public function destroy($id): bool
    {
        return $this->model->deletesBy($id);
    }

    /**
     * 文章发布
     *
     * @urlParam id int required
     *
     * @param $id
     * @return bool
     */
    public function enable($id): bool
    {
        return $this->model->togglesBy($id);
    }
}
