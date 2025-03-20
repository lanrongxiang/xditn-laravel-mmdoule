<?php

namespace Xditn\Base\modules\Pay\Support;

use Psr\Http\Message\ResponseInterface;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

/**
 * @method ResponseInterface|Rocket web(array $order) 网页支付
 * @method ResponseInterface|Rocket h5(array $order) H5 支付
 * @method ResponseInterface|Rocket app(array $order) APP 支付
 * @method Rocket|Collection mini(array $order) 小程序支付
 * @method Rocket|Collection pos(array $order) 刷卡支付
 * @method Rocket|Collection scan(array $order) 扫码支付
 * @method Rocket|Collection transfer(array $order) 账户转账
 */
class AliPay extends Pay
{
    public function pay(array $params): array
    {

    }

    public function notify(array $params): array
    {
        // TODO: Implement notify() method.
    }

    /**
     * @return \Yansongda\Pay\Provider\Alipay
     *
     * @throws \Yansongda\Artful\Exception\ContainerException
     */
    protected function instance()
    {
        // TODO: Implement instance() method.
        Pay::config(config('pay.alipay'));

        return Pay::alipay();
    }
}
