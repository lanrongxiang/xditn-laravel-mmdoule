<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Shop\Models;

use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $name
 * @property $icon
 * @property $description
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class ProductServices extends Model
{
    protected $table = 'shop_product_services';

    protected $fillable = ['id', 'name', 'icon', 'description', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    protected array $fields = ['id', 'name', 'icon', 'description', 'created_at'];

    protected array $form = ['name', 'icon', 'description'];

    public array $searchable = [

    ];
}
