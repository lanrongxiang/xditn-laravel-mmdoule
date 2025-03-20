<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Shop\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $title
 * @property $price
 * @property $handsel_price
 * @property $sort
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class VipRechargePlans extends Model
{
    protected $table = 'shop_vip_recharge_plans';

    protected $fillable = ['id', 'title', 'price', 'handsel_price', 'sort', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    protected array $fields = ['id', 'title', 'price', 'handsel_price', 'sort', 'created_at', 'updated_at'];

    protected array $form = ['title', 'price', 'handsel_price', 'sort'];

    public array $searchable = [
        'title' => 'like',
    ];

    public function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    public function handselPrice(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }
}
