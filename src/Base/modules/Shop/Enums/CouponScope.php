<?php

namespace Xditn\Base\modules\Shop\Enums;

use Xditn\Enums\Enum;

enum CouponScope: int implements Enum
{
    // 全场通用
    case FULL = 1;

    // 指定商品
    case PRODUCTS = 2;

    public function value(): string
    {
        // TODO: Implement value() method.
        return match ($this) {
            self::FULL => 1,
            self::PRODUCTS => 2,
        };
    }

    public function name(): string
    {
        // TODO: Implement name() method.
        return match ($this) {
            self::FULL => '全场通用',
            self::PRODUCTS => '指定商品',
        };
    }

    public function assert(int $value): bool
    {
        return $this->value === $value;
    }
}
