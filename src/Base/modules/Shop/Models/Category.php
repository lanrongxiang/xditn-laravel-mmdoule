<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Shop\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Xditn\Base\modules\Shop\Models\Pivots\CategoryHasProducts;
use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $parent_id
 * @property $name
 * @property $sort
 * @property $status
 * @property $title
 * @property $keywords
 * @property $descriptions
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class Category extends Model
{
    protected $table = 'shop_category';

    protected $fillable = ['id', 'parent_id', 'name', 'icon', 'sort', 'status', 'title', 'keywords', 'descriptions', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    protected array $fields = ['id', 'parent_id', 'icon', 'name', 'sort', 'status', 'title', 'keywords', 'descriptions', 'created_at'];

    protected array $form = ['parent_id', 'name', 'sort', 'icon', 'status', 'title', 'keywords', 'descriptions'];

    protected bool $asTree = true;

    protected bool $isPaginate = false;

    public array $searchable = [
        'name' => 'like',
    ];

    /**
     * 分类相关产品
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Products::class,
            CategoryHasProducts::class,
            'category_id',
            'product_id'
        );
    }

    public function getProductsBy($categoryIds, $limit, $sort): mixed
    {
        $productIds = CategoryHasProducts::whereIn('category_id', explode(',', $categoryIds))->pluck('product_id')->unique();

        return Products::whereIn('id', $productIds)
            ->when($sort != 'all', function ($query) use ($sort) {
                $query->orderByDesc($sort);
            })
            ->limit($limit)
            ->get(['id',  'title',  'images', 'sales', 'price', 'list_price']);
    }
}
