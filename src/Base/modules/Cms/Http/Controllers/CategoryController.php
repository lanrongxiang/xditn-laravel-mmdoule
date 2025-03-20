<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Cms\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\Cms\Models\Category;
use Xditn\Base\XditnController as Controller;
use Xditn\Exceptions\FailedException;

/**
 * @group 内容管理
 *
 * @subgroup 分类管理
 * @subgroupDescription MModule 后台内容管理->分类管理
 */
class CategoryController extends Controller
{
    public function __construct(
        protected readonly Category $model
    ) {
    }

    /**
     * 分类列表
     *
     * @queryParam name string 分类名称
     *
     * @responseField code int 状态码
     * @responseField message string 提示信息
     * @responseField data object[] 分类数据
     * @responseField data[].id int 分类ID
     * @responseField data[].name string 分类名称
     * @responseField data[].slug string 分类别名
     * @responseField data[].parent_id int 父级ID
     * @responseField data[].url string 分类链接
     * @responseField data[].children object[] 子分类
     * @responseField data[].order int 排序
     * @responseField data[].created_at string 创建时间
     *
     * @return mixed
     */
    public function index(): mixed
    {
        $categories = $this->model->getList();

        $transfer = function ($categories, $url = '/') use (&$transfer) {
            if (! count($categories)) {
                return [];
            }

            foreach ($categories as $category) {
                $category->url = $url.$category->slug;
                if (isset($category['children']) && count($category['children'])) {
                    $transfer($category['children'], $category->url.'/');
                }
            }

            return $categories;
        };

        return $transfer($categories);
    }

    /**
     * 新增分类
     *
     * @bodyParam name string required 分类名称
     * @bodyParam slug string required 分类别名
     * @bodyParam parent_id int required 父级ID
     * @bodyParam order int required 排序
     * @bodyParam type int 类型
     *
     * @responseField data bool
     *
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request): mixed
    {
        if ($this->model->where('slug', $request->get('slug'))->first()) {
            throw new FailedException('分类别名已存在, 请重新设置');
        }

        return $this->model->storeBy($request->all());
    }

    /**
     * 分类详情
     *
     * @urlParam id int 分类ID
     *
     * @responseField name string 分类名称
     * @responseField slug string 分类别名
     * @responseField parent_id int 父级ID
     * @responseField order int 排序
     * @responseField type int 类型
     *
     * @param $id
     * @return mixed
     */
    public function show($id): mixed
    {
        return $this->model->firstBy($id);
    }

    /**
     * 更新分类
     *
     * @urlParam id int required 分类ID
     *
     * @bodyParam name string required 分类名称
     * @bodyParam slug string required 分类别名
     * @bodyParam parent_id int required 父级ID
     * @bodyParam order int required 排序
     * @bodyParam type int 类型
     *
     * @responseField data bool
     *
     * @param $id
     * @param  Request  $request
     * @return mixed
     */
    public function update($id, Request $request): mixed
    {
        if ($this->model->where('slug', $request->get('slug'))
            ->where('id', '<>', $id)->first()) {
            throw new FailedException('分类别名已存在, 请重新设置');
        }

        if ($id == $request->get('parent_id')) {
            throw new FailedException('不能选择自身作为父级');
        }

        return $this->model->updateBy($id, $request->all());
    }

    /**
     * 删除分类
     *
     * @urlParam id int required 分类ID
     *
     * @responseField data bool
     *
     * @param $id
     * @return bool|null
     */
    public function destroy($id): ?bool
    {
        return $this->model->deleteBy($id);
    }
}
