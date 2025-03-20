<?php

namespace Xditn\Base\modules\Openapi\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Context;
use Xditn\Base\modules\Openapi\Enums\Code;
use Xditn\Base\modules\Openapi\Enums\Enum;

class OpenapiResponse
{
    /**
     * 成功响应
     */
    public static function success(mixed $data, string $message = 'success', Code $code = Code::SUCCESS): JsonResponse
    {
        return response()->json([
            'code' => $code->value,
            'message' => $message,
            'data' => $data,
            'trace' => self::getTrace(),
        ]);
    }

    /**
     * 错误响应
     */
    public static function error(string $message = 'api error', int|Code $code = Code::FAILED): JsonResponse
    {
        return response()->json([
            'code' => $code instanceof Enum ? $code->value : $code,
            'message' => $message,
            'trace' => self::getTrace(),
        ]);
    }

    /**
     * 分页响应
     */
    public static function paginate(LengthAwarePaginator $paginator, string $message = 'success', Code $code = Code::SUCCESS): JsonResponse
    {
        return response()->json([
            'code' => $code->value,
            'message' => $message,
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'limit' => $paginator->perPage(),
            'page' => $paginator->currentPage(),
            'trace' => self::getTrace(),
        ]);
    }

    /**
     * 获取请求 trace 信息
     *
     * @return array
     */
    protected static function getTrace(): array
    {
        $requestId = Context::get('openapi_request_id');
        Context::forget('openapi_request_id');
        $now = time();

        return [
            'request_id' => $requestId,
            'timestamp' => $now,
            'take_time' => $now - (int) LARAVEL_START,
        ];
    }
}
