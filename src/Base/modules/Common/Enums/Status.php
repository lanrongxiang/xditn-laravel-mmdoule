<?php

namespace Xditn\Base\modules\Common\Enums;

use Xditn\Enums\Enum;

enum Status: int implements Enum
{
    case ENABLE = 1;
    case INVOKE = 2;

    public function value(): int
    {
        // TODO: Implement value() method.
        return match ($this) {
            self::ENABLE => 1,
            self::INVOKE => 2,
        };
    }

    public function name(): string
    {
        // TODO: Implement name() method.
        return match ($this) {
            self::ENABLE => '启用',
            self::INVOKE => '禁用',
        };
    }

    public function assert(int $value): bool
    {
        return $this->value === $value;
    }
}
