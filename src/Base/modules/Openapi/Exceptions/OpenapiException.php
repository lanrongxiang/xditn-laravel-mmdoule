<?php

namespace Xditn\Base\modules\Openapi\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Xditn\Base\modules\Openapi\Enums\Code;
use Xditn\Base\modules\Openapi\Enums\Enum;

/**
 * @class ApiException
 */
abstract class OpenapiException extends HttpException
{
    //
    public function __construct(
        string $message = '',
        Code $code = Code::FAILED
    ) {
        if ($this->code instanceof Enum) {
            $code = $this->code;

            $this->message = $this->code->message();
        }

        parent::__construct(
            $this->statusCode(),
            $message ?: $this->message,
            null,
            [],
            $code->value
        );
    }

    /**
     * @return int
     */
    protected function statusCode(): int
    {
        return 500;
    }
}
