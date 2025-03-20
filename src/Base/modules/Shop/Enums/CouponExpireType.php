<?php

namespace Xditn\Base\modules\Shop\Enums;

use Xditn\Enums\Enum;

enum CouponExpireType: int implements Enum
{
    // 领取后生效
    case RECEIVE_EFFECT = 1;

    // 固定时间
    case FIX_TIME = 2;

    public function value(): string
    {
        // TODO: Implement value() method.
        return match ($this) {
            self::RECEIVE_EFFECT => 1,
            self::FIX_TIME => 2,
        };
    }

    public function name(): string
    {
        // TODO: Implement name() method.
        return match ($this) {
            self::RECEIVE_EFFECT => '领取后生效',
            self::FIX_TIME => '固定时间',
        };
    }

    public function assert(int $value): bool
    {
        return $this->value === $value;
    }
}
