<?php

namespace Xditn\Base\modules\Shop\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Xditn\Base\XditnModel as Model;

class ProductSku extends Model
{
    protected $table = 'shop_product_sku';

    protected $fillable = [
        'id', 'product_id', 'spec_id',

        'images', 'product_no', 'price', 'list_price', 'cost_price',

        'weight', 'volume', 'stock', 'alert_stock',

        'created_at', 'updated_at',
    ];

    public function price(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    public function listPrice(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    public function costPrice(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    public function weight(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    public function volume(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    /**
     * images attribute
     *
     * @return Attribute
     */
    public function images(): Attribute
    {
        return new Attribute(
            get: fn ($value) => json_decode($value, true),
        );
    }
}
