<?php

namespace Xditn\Base\modules\Openapi\Exceptions;

use Xditn\Base\modules\Openapi\Enums\Code;

class RateLimiterException extends OpenapiException
{
    protected $code = Code::RATE_LIMIT;
}
