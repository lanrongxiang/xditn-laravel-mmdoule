<?php

namespace Xditn\Base\modules\Shop\Enums;

use Xditn\Enums\Enum;

enum CouponType: int implements Enum
{
    case FULL_REDUCE = 1; // 满减券类型
    case DISCOUNT = 2; // 折扣券类型

    public function value(): string
    {
        // TODO: Implement value() method.
        return match ($this) {
            self::FULL_REDUCE => 1,
            self::DISCOUNT => 2,
        };
    }

    public function name(): string
    {
        // TODO: Implement name() method.
        return match ($this) {
            self::FULL_REDUCE => '满减券',
            self::DISCOUNT => '折扣券',
        };
    }

    public function assert(int $value): bool
    {
        return $this->value === $value;
    }
}
