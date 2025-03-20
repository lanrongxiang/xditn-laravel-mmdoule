<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Shop\Http\Controllers;

use Xditn\Base\modules\Shop\Http\Requests\SeckillActivityRequest as Request;
use Xditn\Base\modules\Shop\Models\SeckillActivity;
use Xditn\Base\XditnController as Controller;
use Xditn\Exceptions\FailedException;

class SeckillActivityController extends Controller
{
    public function __construct(
        protected readonly SeckillActivity $model
    ) {
    }

    public function index(): mixed
    {
        return $this->model->setBeforeGetList(function ($query) {
            return $query->join('shop_seckill_products', 'shop_seckill_products.id', '=', 'shop_seckill_activity.seckill_product_id')
                ->join('shop_products', 'shop_products.id', '=', 'shop_seckill_products.product_id')
                ->select('shop_seckill_activity.*', 'shop_products.title as product_title')
                ->when(request('title'), function ($query) {
                    return $query->whereLike('shop_products.title', request('title'));
                });
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
        $activity = $this->model->firstBy($id);

        $activity->is_processing = $activity->isProcessing();

        return $activity;
    }

    /**
     * @return mixed
     */
    public function update($id, Request $request)
    {
        if ($this->model->firstBy($id)->isProcessing()) {
            throw new FailedException('秒杀活动正在进行中, 无法编辑');
        }

        return $this->model->updateBy($id, $request->all());
    }

    /**
     * @return mixed
     */
    public function destroy($id)
    {
        if ($this->model->firstBy($id)->isProcessing()) {
            throw new FailedException('秒杀活动正在进行中, 无法删除');
        }

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
