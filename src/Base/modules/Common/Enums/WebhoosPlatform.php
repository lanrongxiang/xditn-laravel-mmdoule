<?php

namespace Xditn\Base\modules\Common\Enums;

use Xditn\Enums\Enum;

enum WebhoosPlatform: int implements Enum
{
    case DINGTALK = 1;
case FEISHU = 2;
case WORK_WEIXIN = 3;

    public function value(): int
    {
        // TODO: Implement value() method.
        return match ($this) {
           self::DINGTALK => 1,
self::FEISHU => 2,
self::WORK_WEIXIN => 3,
        };
    }

    public function name(): string
    {
        // TODO: Implement name() method.
        return match ($this) {
            self::DINGTALK => '钉钉',
self::FEISHU => '飞书',
self::WORK_WEIXIN => '企微',
        };
    }

    public function assert(int $value): bool
    {
        return $this->value === $value;
    }
}
