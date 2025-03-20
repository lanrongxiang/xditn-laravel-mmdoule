<?php

namespace Xditn\Base\modules\Permissions\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Xditn\Enums\Code;
use Xditn\Exceptions\XditnException;

class PermissionForbidden extends XditnException
{
    protected $message = 'permission forbidden';

    protected $code = Code::PERMISSION_FORBIDDEN;

    public function statusCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }
}
