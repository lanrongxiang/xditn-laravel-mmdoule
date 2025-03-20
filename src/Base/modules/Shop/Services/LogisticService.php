<?php

namespace Xditn\Base\modules\Shop\Services;

use Xditn\Base\modules\Shop\Services\Logistics\LogisticFactory;

class LogisticService
{
    public function search(string $orderNo)
    {
        return app(LogisticFactory::make(LogisticFactory::GLOBAL_LOGISTIC))->traces([
            'order_no' => $orderNo,
        ]);
    }

    public function expressLists()
    {
        return app(LogisticFactory::make(LogisticFactory::GLOBAL_LOGISTIC))->expressLists();
    }
}
