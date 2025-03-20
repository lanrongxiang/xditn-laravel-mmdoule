<?php

namespace Xditn\Base\modules\Shop\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CategoryHasProducts extends Pivot
{
    protected $table = 'shop_category_has_products';

    public $timestamps = false;
}
