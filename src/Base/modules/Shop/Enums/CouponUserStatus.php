<?php

namespace Xditn\Base\modules\Shop\Enums;

use Xditn\Enums\Enum;

enum CouponUserStatus: int implements Enum
{
    case USED = 1; // 已使用
    case UN_USED = 0; // 未使用

    public function value(): string
    {
        // TODO: Implement value() method.
        return match ($this) {
            self::USED => 1,
            self::UN_USED => 2,
        };
    }

    public function name(): string
    {
        // TODO: Implement name() method.
        return match ($this) {
            self::USED => '已使用',
            self::UN_USED => '未使用',
        };
    }

    public function assert(int $value): bool
    {
        return $this->value === $value;
    }
}
