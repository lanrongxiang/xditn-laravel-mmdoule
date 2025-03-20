<?php

declare(strict_types=1);

namespace Xditn\Exceptions;

use Xditn\Enums\Code;

class WebhookException extends XditnException
{
    protected $code = Code::WEBHOOK_FAILED;
}
