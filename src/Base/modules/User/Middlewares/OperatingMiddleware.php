<?php

namespace Xditn\Base\modules\User\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Xditn\Base\modules\User\Models\LogOperate;

class OperatingMiddleware
{
    public function handle($request, Closure $next): mixed
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        app(LogOperate::class)->log($request, $response);
    }
}
