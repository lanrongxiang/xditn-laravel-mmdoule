<?php

declare(strict_types=1);

namespace Xditn\Exceptions;

use Xditn\Enums\Code;

class ComponentNotFoundException extends XditnException
{
    protected $code = Code::COMPONENT_NOT_FOUND;
}
