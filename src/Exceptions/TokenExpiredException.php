<?php

declare(strict_types=1);

namespace Xditn\Exceptions;

use Xditn\Enums\Code;

class TokenExpiredException extends XditnException
{
    protected $code = Code::TOKEN_EXPIRED;
}
