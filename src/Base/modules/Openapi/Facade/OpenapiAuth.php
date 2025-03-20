<?php

namespace Xditn\Base\modules\Openapi\Facade;

use Illuminate\Support\Facades\Facade;
use Xditn\Base\modules\Openapi\Models\Users;

/**
 * @method static bool check(string $appKey, string $sign, array $data)
 * @method static Users getUser()
 * @method static int getUserId()
 *
 * @mixin \Xditn\Base\modules\Openapi\Support\OpenapiAuth
 */
class OpenapiAuth extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return \Xditn\Base\modules\Openapi\Support\OpenapiAuth::class;
    }
}
