<?php

namespace Xditn\Base\modules\Pay\Providers;

use Xditn\Providers\XditnModuleServiceProvider;

class PayServiceProvider extends XditnModuleServiceProvider
{
    /**
     * route path
     *
     * @return string
     */
    public function moduleName(): string
    {
        // TODO: Implement path() method.
        return 'Pay';
    }
}
