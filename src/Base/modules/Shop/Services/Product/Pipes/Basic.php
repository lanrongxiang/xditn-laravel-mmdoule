<?php

namespace Xditn\Base\modules\Shop\Services\Product\Pipes;

use Closure;
use Illuminate\Support\Str;
use Xditn\Base\modules\Shop\Models\Products;

class Basic
{
    public function handle(array $product, Closure $next): mixed
    {
        /**
         * @var array{type: int,title: string,keywords: string,
         *       subtitle: string,images: array,video: string,brand_id: int,
         *       category_ids: array,service_ids: array,
         *       tag_ids:array,unit: string,virtual_sales: number,sort: number,
         *       is_available: number,is_schedule: number,
         *       schedule_time: number} $basic
         */
        $basic = $product['basic'];

        $scheduleTime = 0;
        if ($basic['is_schedule'] == Products::SCHEDULE) {
            if ($basic['schedule_time']) {
                $scheduleTime = strtotime($basic['schedule_time']);
            }
        }

        $basic = array_merge([
            'type' => $basic['type'],
            'is_specifications' => $product['specifications']['is_specifications'],
            'title' => $basic['title'],
            'keywords' => $basic['keywords'],
            'subtitle' => $basic['subtitle'],
            'images' => json_encode($basic['images']),
            'video' => $basic['video'],
            'brand_id' => $basic['brand_id'] ?? 0,
            'is_available' => $basic['is_available'],
            'is_schedule' => $basic['is_schedule'],
            'schedule_time' => $scheduleTime,
            'unit' => $basic['unit'],
            'virtual_sales' => $basic['virtual_sales'],
            'sort' => $basic['sort'],
            'category_ids' => $this->getCategoryIds($basic['category_ids']),
            'service_ids' => $basic['service_ids'],
            'tag_ids' => $basic['tag_ids'],
        ], $this->getDefaultSku($product));

        $product['basic'] = $basic;

        return $next($product);
    }

    /**
     * 获取分类IDs
     */
    protected function getCategoryIds(array $categoryIds): array
    {
        $_categoryIds = [];

        foreach ($categoryIds as $categoryId) {
            $_categoryIds[] = is_array($categoryId) ? array_pop($categoryId) : $categoryId;
        }

        return $_categoryIds;
    }

    protected function getDefaultSku(array $product)
    {
        if ($product['specifications']['is_specifications'] == Products::SIGNAL_SPECIFICATION) {
            $sku = $product['specifications']['sku'];
        } else {
            $sku = $product['specifications']['skus'][0];
            unset($sku['images']);

            $sku['stock'] = array_sum(array_column($product['specifications']['skus'], 'stock'));
            $sku['alert_stock'] = array_sum(array_column($product['specifications']['skus'], 'alert_stock'));
        }

        if (! $sku['product_no']) {
            $sku['product_no'] = $this->getProductNo();
        }

        return $sku;
    }

    protected function getProductNo(): string
    {
        return strtoupper('p'.Str::random(4).'-'.microtime(true) * 10000 .'-'.Str::random(4));
    }
}
