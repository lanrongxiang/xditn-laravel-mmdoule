<?php

namespace Xditn\Base\modules\Shop\Providers;

use Xditn\Providers\XditnModuleServiceProvider;

class ShopServiceProvider extends XditnModuleServiceProvider
{
    /**
     * route path
     */
    public function moduleName(): string
    {
        // TODO: Implement path() method.
        return 'shop';
    }
}
