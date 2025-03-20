<?php

namespace Xditn\Base\modules\Cms\Enums;

use Xditn\Enums\Enum;

enum ResourceType: int implements Enum
{
    case CAROUSEL = 1; // 轮播
    case FRIEND_LINK = 2; // 友情链接
    case AD = 3; // 广告

    public function value(): int
    {
        // TODO: Implement value() method.
        return match ($this) {
            self::CAROUSEL => 1,
            self::FRIEND_LINK => 2,
            self::AD => 3,
        };
    }

    public function name(): string
    {
        // TODO: Implement name() method.
        return match ($this) {
            self::CAROUSEL => '轮播图',
            self::FRIEND_LINK => '友情链接',
            self::AD => '广告',
        };
    }

    public function assert(int $value): bool
    {
        return $this->value === $value;
    }
}
