<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Shop\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $product_id
 * @property $seckill_price
 * @property $stock
 * @property $limit_per_user
 * @property $stock_reduce_type
 * @property $status
 * @property $sort
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class SeckillProducts extends Model
{
    protected $table = 'shop_seckill_products';

    protected $fillable = ['id', 'product_id', 'seckill_price', 'stock', 'limit_per_user', 'stock_reduce_type', 'status', 'sort', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    protected array $fields = ['id', 'product_id', 'seckill_price', 'stock', 'limit_per_user', 'stock_reduce_type', 'status', 'sort', 'created_at'];

    protected array $form = ['product_id', 'seckill_price', 'stock', 'limit_per_user', 'stock_reduce_type', 'status', 'sort'];

    public array $searchable = [
        'title' => 'like', // 商品名称
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class, 'product_id', 'id');
    }

    /**
     * store by
     */
    public function storeBy(array $params): ?Model
    {
        $productIds = $params['product_id'];
        unset($params['product_id']);

        foreach ($productIds as $productId) {
            $params['product_id'] = $productId;
            $this->createBy($params);
        }

        return true;
    }

    public function updateBy($id, array $params): bool
    {
        $params['product_id'] = $params['product_id'][0];

        return parent::updateBy($id, $params);
    }
}
