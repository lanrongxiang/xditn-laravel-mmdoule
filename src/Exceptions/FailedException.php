<?php

namespace Xditn\Exceptions;

use Xditn\Enums\Code;

/**
 * 自定义失败异常类
 */
class FailedException extends XditnException
{
    protected $code = Code::FAILED;
}
