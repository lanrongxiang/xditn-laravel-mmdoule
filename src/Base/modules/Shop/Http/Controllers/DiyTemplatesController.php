<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\Shop\Models\Category;
use Xditn\Base\modules\Shop\Models\DiyTemplates;
use Xditn\Base\XditnController as Controller;

class DiyTemplatesController extends Controller
{
    public function __construct(
        protected readonly DiyTemplates $model
    ) {
    }

    /**
     * @return mixed
     */
    public function index(): mixed
    {
        return $this->model->getList();
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request)
    {
        return $this->model->storeBy($request->all());
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        return $this->model->firstBy($id);
    }

    /**
     * @param  Request  $request
     * @param $id
     * @return mixed
     */
    public function update($id, Request $request)
    {
        return $this->model->updateBy($id, $request->all());
    }

    /**
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->model->deleteBy($id);
    }

    /**
     * 获取DIY商品
     *
     * @param  Request  $request
     * @param  Category  $category
     * @return mixed
     */
    public function getProducts(Request $request, Category $category)
    {
        return $category->getProductsBy($request->get('category_ids'), $request->get('limit'), $request->get('sort'));
    }
}
