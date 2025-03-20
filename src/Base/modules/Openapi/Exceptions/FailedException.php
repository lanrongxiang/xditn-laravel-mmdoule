<?php

namespace Xditn\Base\modules\Openapi\Exceptions;

use Xditn\Base\modules\Openapi\Enums\Code;

class FailedException extends OpenapiException
{
    //
    protected $code = Code::FAILED;
}
