<?php

namespace Xditn\Base\modules\Shop\Services\Product\Pipes;

use Closure;
use Xditn\Base\modules\Shop\Models\Products;

class Specifications
{
    public function handle(array $product, Closure $next): mixed
    {
        $specifications = $product['specifications'];

        // 判断是不是更新
        $id = $product['id'] ?? null;
        if (! $id) {
            $product['specifications'] = $this->getSpecifications($specifications);
        }

        $product['specifications'] = $this->addSpecValues($product['specifications']);

        // 换算价格
        foreach ($product['specifications']['skus'] as &$sku) {
            $sku['price'] = $sku['price'] * 100;
            $sku['list_price'] = $sku['list_price'] * 100;
            $sku['cost_price'] = $sku['cost_price'] * 100;
            $sku['weight'] = $sku['weight'] * 100;
            $sku['volume'] = $sku['volume'] * 100;
        }

        return $next($product);
    }

    /**
     * 获取规格属性值
     */
    protected function getSpecifications(array $specifications): array
    {
        $specs = [];

        if ($specifications['is_specifications'] == Products::MULTI_SPECIFICATION) {
            foreach ($specifications['specs'] as $spec) {
                foreach ($spec['values'] as $value) {
                    $specs[$spec['name']][] = $value['value'];
                }
            }
        }

        $specifications['specs'] = $specs;

        return $specifications;
    }

    protected function addSpecValues(array $specifications): array
    {
        if ($specifications['is_specifications'] == Products::MULTI_SPECIFICATION) {
            foreach ($specifications['skus'] as &$sku) {
                foreach ($sku as $k => $value) {
                    if (str_starts_with($k, 's_')) {
                        $sku['spec_values'][] = $value;
                        unset($sku[$k]);
                    }
                }
            }
        }

        return $specifications;
    }
}
