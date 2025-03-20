<?php

namespace Xditn\Base\modules\Shop\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductHasServices extends Pivot
{
    protected $table = 'shop_product_has_services';

    public $timestamps = false;
}
