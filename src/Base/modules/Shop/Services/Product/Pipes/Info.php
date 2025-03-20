<?php

namespace Xditn\Base\modules\Shop\Services\Product\Pipes;

use Closure;

class Info
{
    /**
     * @param  array  $product
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(array $product, Closure $next): mixed
    {
        // 暂不处理
        $info = $product['info'];

        $info['params'] = $product['config']['params'];

        $product['info'] = $info;

        return $next($product);
    }
}
