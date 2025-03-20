<?php

namespace Xditn\Base\modules\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\System\Models\SystemConfig;
use Xditn\Base\modules\System\Support\Configure;
use Xditn\Base\XditnController as Controller;

class ConfigController extends Controller
{
    /**
     * 免邮配置
     *
     * @param  Request  $request
     * @param  Configure  $configure
     * @param  SystemConfig  $config
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|mixed
     */
    public function freeShipping(Request $request, Configure $configure, SystemConfig $config)
    {
        if ($request->isMethod('POST')) {
            return $config->storeBy(
                $configure->parse('ship', $request->all()
                ));
        } else {
            return config('ship');
        }
    }

    /**
     * vip 充值配置
     *
     * @param  Request  $request
     * @param  Configure  $configure
     * @param  SystemConfig  $config
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|mixed
     */
    public function vipRecharge(Request $request, Configure $configure, SystemConfig $config)
    {
        if ($request->isMethod('POST')) {
            return $config->storeBy(
                $configure->parse('recharge', $request->all()
                ));
        } else {
            return config('recharge');
        }
    }

    /**
     * 物流配置
     *
     * @param  Request  $request
     * @param  Configure  $configure
     * @param  SystemConfig  $config
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|mixed
     */
    public function logistics(Request $request, Configure $configure, SystemConfig $config)
    {
        if ($request->isMethod('POST')) {
            return $config->storeBy(
                $configure->parse('logistics', $request->all()
                ));
        } else {
            return config('logistics');
        }
    }
}
