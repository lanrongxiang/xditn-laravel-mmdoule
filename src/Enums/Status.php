<?php

namespace Xditn\Enums;

enum Status: int implements Enum
{
    case Enable = 1;

    case Disable = 2;

    /**
     * @desc name
     */
    public function name(): string
    {
        return match ($this) {
            Status::Enable => '启用',

            Status::Disable => '禁用'
        };
    }

    /**
     * get value
     *
     * @return int
     */
    public function value(): int
    {
        return match ($this) {
            Status::Enable => 1,

            Status::Disable => 2,
        };
    }

    /**
     * 断言
     *
     * @param $value
     * @return bool
     */
    public function assert($value): bool
    {
        return $this->value() == $value;
    }
}
