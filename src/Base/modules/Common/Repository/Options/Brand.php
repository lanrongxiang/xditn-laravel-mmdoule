<?php

namespace Xditn\Base\modules\Common\Repository\Options;

use Illuminate\Support\Collection;
use Xditn\Base\modules\Shop\Models\ProductBrand;

class Brand implements OptionInterface
{
    public function get(): Collection
    {

        return ProductBrand::all(['id as value', 'name as label']);
    }
}
