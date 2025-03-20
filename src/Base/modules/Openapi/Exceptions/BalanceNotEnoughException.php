<?php

namespace Xditn\Base\modules\Openapi\Exceptions;

use Xditn\Base\modules\Openapi\Enums\Code;

class BalanceNotEnoughException extends OpenapiException
{
    protected $code = Code::POINT_NOT_ENOUGH;
}
