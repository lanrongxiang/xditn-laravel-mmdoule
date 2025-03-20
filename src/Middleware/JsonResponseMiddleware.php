<?php

namespace Xditn\Middleware;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * JSON 响应中间件
 */
class JsonResponseMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        $response = $next($request);
        // 设置允许的头信息
        $response->headers->set('Access-Control-Expose-Headers', 'filename,write_type');
        // 处理二进制文件响应
        if ($response instanceof BinaryFileResponse) {
            return $response;
        }
        // 处理其他响应
        if ($response instanceof Response) {
            return new JsonResponse($response->getContent());
        }

        return $response;
    }
}
