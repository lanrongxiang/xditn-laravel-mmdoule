<?php

namespace Xditn\Base\modules\Shop\Enums;

use Xditn\Enums\Enum;

enum ProductStatus: int implements Enum
{
    case AVAILABLE = 1; // 上架
    case NO_AVAILABLE = 2; // 仓库中
    case DELIST = 3; // 下架
    case SOLD_OUT = 4; // 已售罄

    public function value(): string
    {
        // TODO: Implement value() method.
        return match ($this) {
            self::AVAILABLE => 1,
            self::NO_AVAILABLE => 2,
            self::DELIST => 3,
            self::SOLD_OUT => 4
        };
    }

    public function name(): string
    {
        // TODO: Implement name() method.
        return match ($this) {
            self::AVAILABLE => '上架',
            self::NO_AVAILABLE => '仓库中',
            self::DELIST => '下架',
            self::SOLD_OUT => '已售罄'
        };
    }

    public function assert(int $value): bool
    {
        return $this->value === $value;
    }
}
