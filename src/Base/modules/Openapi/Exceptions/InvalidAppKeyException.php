<?php

namespace Xditn\Base\modules\Openapi\Exceptions;

use Xditn\Base\modules\Openapi\Enums\Code;

class InvalidAppKeyException extends OpenapiException
{
    protected $code = Code::INVALID_APP_KEY;
}
