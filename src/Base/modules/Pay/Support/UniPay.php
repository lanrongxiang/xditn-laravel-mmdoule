<?php

namespace Xditn\Base\modules\Pay\Support;

use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

/**
 * @method Collection web(array $order) 公众号支付
 * @method Collection h5(array $order) H5 支付
 * @method Collection pos(array $order) 刷卡支付
 * @method Collection scan(array $order) 扫码支付
 */
class UniPay extends Pay
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
