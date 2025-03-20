<?php

namespace Xditn\Base\modules\Shop\Services\Product\Pipes;

use Closure;

/**
 * 非实物去除邮费相关
 */
class RemoveShip
{
    /**
     * @param  array  $product
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(array $product, Closure $next): mixed
    {
        $ship = $product['ship'];

        // 运费类型设置成 0
        $ship['ship_fee'] = $ship['ship_template_id'] = $ship['ship_type'] = 0;

        $product['basic'] = array_merge($product['basic'], $ship);

        return $next($product);
    }
}
