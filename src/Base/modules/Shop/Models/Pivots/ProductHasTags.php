<?php

namespace Xditn\Base\modules\Shop\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductHasTags extends Pivot
{
    protected $table = 'shop_product_has_tags';

    public $timestamps = false;
}
