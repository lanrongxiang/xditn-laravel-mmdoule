<?php

declare(strict_types=1);

namespace Xditn\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class PermissionExempt
{
    public function __construct(public readonly string|array|null $methods = null)
    {
    }
}
