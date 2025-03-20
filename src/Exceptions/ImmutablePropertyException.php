<?php

declare(strict_types=1);

namespace Xditn\Exceptions;

final class ImmutablePropertyException extends XditnException
{
    public function __construct(string $property)
    {
        parent::__construct("无法更新不可变属性: {$property}");
    }
}
