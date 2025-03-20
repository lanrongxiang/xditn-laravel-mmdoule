<?php

namespace Xditn\Base\modules\Shop\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ProductInfo extends Model
{
    protected $table = 'shop_product_info';

    protected $fillable = [
        'content',
        'params',
    ];

    public $timestamps = false;

    protected function params(): Attribute
    {
        return new Attribute(set: fn ($v) => json_encode($v));
    }
}
