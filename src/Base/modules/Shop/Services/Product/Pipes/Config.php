<?php

namespace Xditn\Base\modules\Shop\Services\Product\Pipes;

use Closure;

class Config
{
    /**
     * @param  array  $product
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(array $product, Closure $next): mixed
    {
        // 暂不处理
        $config = $product['config'];

        $product['config'] = $config;

        return $next($product);
    }
}
