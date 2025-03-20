<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Shop\Models;

use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $name
 * @property $logo
 * @property $sort
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class ProductBrand extends Model
{
    protected $table = 'shop_product_brand';

    protected $fillable = ['id', 'name', 'logo', 'sort', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    protected array $fields = ['id', 'name', 'logo', 'sort', 'created_at'];

    protected array $form = ['name', 'logo', 'sort'];

    public array $searchable = [
        'name' => 'like',
    ];
}
