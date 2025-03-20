<?php

namespace Xditn\Base\modules\Openapi\Enums;

interface Enum
{
    public function equal(mixed $value): bool;
}
