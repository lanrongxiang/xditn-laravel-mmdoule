<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Shop\Http\Controllers;

use Xditn\Base\modules\Shop\Http\Requests\SeckillProductRequest as Request;
use Xditn\Base\modules\Shop\Models\SeckillProducts;
use Xditn\Base\XditnController as Controller;

class SeckillProductsController extends Controller
{
    public function __construct(
        protected readonly SeckillProducts $model
    ) {
    }

    public function index(): mixed
    {
        return $this->model->setBeforeGetList(function ($query) {
            return $query->join('shop_products as p', 'p.id', '=', 'shop_seckill_products.product_id')
                ->select('shop_seckill_products.*', 'p.title as product_title');
        })->getList();
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

    /**
     * @return mixed
     */
    public function enable($id)
    {
        return $this->model->toggleBy($id);
    }
}
