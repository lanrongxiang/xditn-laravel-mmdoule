<?php

namespace Xditn\Base\modules\Pay\Support;

use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

/**
 * @method Collection mp(array $order) 公众号支付
 * @method Collection h5(array $order) H5 支付
 * @method Collection app(array $order) APP 支付
 * @method Collection mini(array $order) 小程序支付
 * @method Collection pos(array $order) 刷卡支付
 * @method Collection scan(array $order) 扫码支付
 * @method Collection transfer(array $order) 转账
 */
class WechatPay extends Pay
{
    public function pay(array $params): array
    {
        // TODO: Implement pay() method.

    }

    public function notify(array $params): array
    {
        // TODO: Implement notify() method.
    }

    /**
     * @return \Yansongda\Pay\Provider\Wechat
     *
     * @throws \Yansongda\Artful\Exception\ContainerException
     */
    protected function instance()
    {
        // TODO: Implement instance() method.
        Pay::config(config('pay.wechat'));

        return Pay::wechat();
    }
}
