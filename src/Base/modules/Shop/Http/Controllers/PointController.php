<?php

namespace Xditn\Base\modules\Shop\Http\Controllers;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Xditn\Base\modules\Shop\Models\UserPointsLog;
use Xditn\Base\modules\System\Models\SystemConfig;
use Xditn\Base\modules\System\Support\Configure;
use Xditn\Base\XditnController as Controller;

class PointController extends Controller
{
    /**
     * @return mixed
     */
    public function index(UserPointsLog $userPointsLog, Request $request)
    {
        return $userPointsLog->setBeforeGetList(function ($query) use ($request) {
            return $query->join('members', 'members.id', '=', 'shop_user_points_log.user_id')
                ->when($request->get('username'), function ($query) use ($request) {
                    $query->whereLike('members.username', $request->get('username'));
                })
                ->when($request->get('start_at'), function ($query) use ($request) {
                    $query->whereLike('shop_user_points_log.created_at', '>=', strtotime($request->get('start_at')));
                })
                ->when($request->get('end_at'), function ($query) use ($request) {
                    $query->whereLike('shop_user_points_log.created_at', '<=', strtotime('+1 day', strtotime($request->get('end_at'))));
                })
                ->select(['shop_user_points_log.*', 'members.username']);
        })->getList();
    }

    /**
     * @return Repository|Application|\Illuminate\Foundation\Application|mixed
     */
    public function setting(Request $request, Configure $configure, SystemConfig $config)
    {
        if ($request->isMethod('POST')) {
            return $config->storeBy(
                $configure->parse('point', $request->all()
                ));
        } else {
            return config('point');
        }
    }
}
