<?php

namespace Xditn\Base\modules\Shop\Services\Logistics;

use Xditn\Exceptions\FailedException;

class LogisticFactory
{
    const GLOBAL_LOGISTIC = 'global';

    public static function make($logistic = 'global'): string
    {
        return match ($logistic) {
            self::GLOBAL_LOGISTIC => GlobalLogistic::class,
            default => throw new FailedException('不支持该物流服务'),
        };
    }
}
