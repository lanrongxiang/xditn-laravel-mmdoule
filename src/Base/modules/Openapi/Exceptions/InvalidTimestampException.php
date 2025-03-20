<?php

namespace Xditn\Base\modules\Openapi\Exceptions;

use Xditn\Base\modules\Openapi\Enums\Code;

class InvalidTimestampException extends OpenapiException
{
    protected $code = Code::INVALID_TIMESTAMP;
}
