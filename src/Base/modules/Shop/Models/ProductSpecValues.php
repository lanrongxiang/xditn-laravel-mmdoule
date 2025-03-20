<?php

namespace Xditn\Base\modules\Shop\Models;

use Xditn\Base\XditnModel;

class ProductSpecValues extends XditnModel
{
    protected $table = 'shop_product_spec_value';

    protected $fillable = [
        'id', 'value', 'creator_id', 'spec_id', 'created_at', 'updated_at',
    ];
}
