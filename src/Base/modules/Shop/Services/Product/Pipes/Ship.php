<?php

namespace Xditn\Base\modules\Shop\Services\Product\Pipes;

use Closure;
use Xditn\Base\modules\Shop\Models\Products;

class Ship
{
    /**
     * @param  array  $product
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(array $product, Closure $next): mixed
    {
        $ship = $product['ship'];

        // 如果是固定运费，则删除模版
        if ($ship['ship_type'] == Products::FIXED_SHIP) {
            $ship['ship_template_id'] = 0;
        }

        // 如果是运费模版，则清楚运费
        if ($ship['ship_type'] == Products::SHIP_TEMPLATE) {
            $ship['ship_fee'] = 0;
        }

        $product['basic'] = array_merge($product['basic'], $ship);

        return $next($product);
    }
}
