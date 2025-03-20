<?php

namespace Xditn\Base\modules\Common\Enums;

use Xditn\Enums\Enum;

enum HttpErrorStatus: int implements Enum
{
    case ERROR = 500;
    case NOT_FOUND = 404;
    case SERVICE_UNAVAILABLE = 503;

    public function value(): int
    {
        // TODO: Implement value() method.
        return match ($this) {
            self::ERROR => 500,
            self::NOT_FOUND => 404,
            self::SERVICE_UNAVAILABLE => 503,
        };
    }

    public function name(): string
    {
        // TODO: Implement name() method.
        return match ($this) {
            self::ERROR => '错误',
            self::NOT_FOUND => '资源未找到',
            self::SERVICE_UNAVAILABLE => '服务不可用',
        };
    }

    public function assert(int $value): bool
    {
        return $this->value === $value;
    }
}
