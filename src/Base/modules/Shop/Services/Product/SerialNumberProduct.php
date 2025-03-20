<?php

namespace Xditn\Base\modules\Shop\Services\Product;

use Xditn\Base\modules\Shop\Services\Product\Pipes\Basic;
use Xditn\Base\modules\Shop\Services\Product\Pipes\Config;
use Xditn\Base\modules\Shop\Services\Product\Pipes\Info;
use Xditn\Base\modules\Shop\Services\Product\Pipes\RemoveShip;
use Xditn\Base\modules\Shop\Services\Product\Pipes\Specifications;

/**
 * 卡密产品
 */
class SerialNumberProduct extends Product
{
    protected array $pipes = [
        Basic::class, // 基本信息
        Specifications::class, // 规格
        Info::class, // 关联信息
        RemoveShip::class, // 删除运费
        Config::class, // 高级设置
    ];
}
