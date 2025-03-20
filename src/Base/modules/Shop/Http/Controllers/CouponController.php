<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\Shop\Http\Requests\CouponRequest;
use Xditn\Base\modules\Shop\Models\Coupon;
use Xditn\Base\modules\Shop\Models\Pivots\UserHasCoupons;
use Xditn\Base\XditnController as Controller;
use Xditn\Exceptions\FailedException;

class CouponController extends Controller
{
    public function __construct(
        protected readonly Coupon $model
    ) {
    }

    public function index(): mixed
    {
        return $this->model->setBeforeGetList(function ($query) {
            return $query->withCount('users');
        })->getList();
    }

    /**
     * @return mixed
     */
    public function store(CouponRequest $request)
    {
        return $this->model->storeBy($request->all());
    }

    /**
     * @return mixed
     */
    public function show($id)
    {
        return $this->model->withCount('users')->find($id);
    }

    /**
     * @return mixed
     */
    public function update($id, CouponRequest $request)
    {
        return $this->model->updateBy($id, $request->all());
    }

    /**
     * @return mixed
     */
    public function destroy($id): mixed
    {
        if ($this->model->has('users')->exists()) {
            throw new FailedException('该优惠券已被用户领取，无法删除');
        }

        return $this->model->deleteBy($id);
    }

    public function enable($id)
    {
        return $this->model->toggleBy($id);
    }

    /**
     * @return bool
     */
    public function give($id, Request $request)
    {
        return $this->model->giveUsers((int) $id, $request->get('users'));
    }

    /**
     * @return mixed
     */
    public function records(UserHasCoupons $userHasCoupons, Request $request)
    {
        return $userHasCoupons->getList($request->all());
    }
}
