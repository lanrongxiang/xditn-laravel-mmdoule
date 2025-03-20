<?php

namespace Xditn\Base\modules\Pay\Support;

use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

/**
 * @method Collection mini(array $order) 小程序支付
 */
class DouYinPay extends Pay
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
     * @return \Yansongda\Pay\Provider\Douyin
     *
     * @throws \Yansongda\Artful\Exception\ContainerException
     */
    protected function instance()
    {
        // TODO: Implement instance() method.
        Pay::config(config('pay.douyin'));

        return Pay::douyin();
    }
}
