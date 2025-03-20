<?php

namespace Xditn\Base\modules\Openapi\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Xditn\Base\modules\Openapi\Enums\Code;
use Xditn\Base\modules\Openapi\Exceptions\InvalidSignatureException;
use Xditn\Base\modules\Openapi\Exceptions\LostException;
use Xditn\Base\modules\Openapi\Facade\OpenapiAuth;

/**
 * 校验签名
 */
class CheckSignatureMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $appKey = $request->header('app-key');
        $sign = $request->header('signature');
        // 为每次请求添加 request id
        Context::add('openapi_request_id', Str::uuid7());

        return match (true) {
            ! $appKey => throw new LostException(Code::APP_KEY_LOST->name(), Code::APP_KEY_LOST),
            ! $sign => throw new LostException(Code::SIGNATURE_LOST->name(), Code::SIGNATURE_LOST),
            ! OpenapiAuth::check($appKey, $sign, $request->all()) => throw new InvalidSignatureException,
            default => $next($request)
        };
    }
}
