<?php

namespace Xditn\Base\modules\Shop\Enums;

use Xditn\Enums\Enum;

enum ProductTypes: int implements Enum
{
    case PHYSICAL = 1; // 实物产品
    case SERIAL_NUMBER = 2; // 卡密
    case VIRTUAL = 3; // 虚拟产品

    public function value(): string
    {
        // TODO: Implement value() method.
        return match ($this) {
            self::PHYSICAL => 1,
            self::SERIAL_NUMBER => 2,
            self::VIRTUAL => 3,
        };
    }

    public function name(): string
    {
        // TODO: Implement name() method.
        return match ($this) {
            self::PHYSICAL => '实物产品',
            self::SERIAL_NUMBER => '卡密产品',
            self::VIRTUAL => '虚拟产品'
        };
    }

    public function assert(int $value): bool
    {
        return $this->value === $value;
    }
}
