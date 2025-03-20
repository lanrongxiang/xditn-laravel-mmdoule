<?php

namespace Xditn\Base\modules\Openapi\Facade;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Facade;
use Xditn\Base\modules\Openapi\Enums\Code;

/**
 * openapi response facade
 *
 * @method static JsonResponse success(mixed $data, string $message = 'success', Code $code = Code::SUCCESS)
 * @method static JsonResponse error(string $message = 'api error', int|Code $code = Code::FAILED)
 * @method static JsonResponse paginate(LengthAwarePaginator $paginator, string $message = 'success', Code $code = Code::SUCCESS)
 *
 * @mixin \Xditn\Base\modules\Openapi\Support\OpenapiResponse
 */
class OpenapiResponse extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return \Xditn\Base\modules\Openapi\Support\OpenapiResponse::class;
    }
}
