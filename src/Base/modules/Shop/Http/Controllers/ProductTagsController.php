<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\Shop\Models\ProductTags;
use Xditn\Base\XditnController as Controller;

class ProductTagsController extends Controller
{
    public function __construct(
        protected readonly ProductTags $model
    ) {
    }

    public function index(): mixed
    {
        return $this->model->getList();
    }

    /**
     * @return mixed
     */
    public function store(Request $request)
    {
        return $this->model->storeBy($request->all());
    }

    /**
     * @return mixed
     */
    public function show($id)
    {
        return $this->model->firstBy($id);
    }

    /**
     * @return mixed
     */
    public function update($id, Request $request)
    {
        return $this->model->updateBy($id, $request->all());
    }

    /**
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->model->deleteBy($id);
    }
}
