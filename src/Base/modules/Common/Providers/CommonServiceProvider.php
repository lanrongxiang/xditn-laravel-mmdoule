<?php

namespace Xditn\Base\modules\Common\Providers;

use Xditn\Base\modules\Common\Console\Area;
use Xditn\Providers\XditnModuleServiceProvider;

class CommonServiceProvider extends XditnModuleServiceProvider
{
    protected array $commands = [
        Area::class,
    ];

    /**
     * route path
     */
    public function moduleName(): string|array
    {
        // TODO: Implement path() method.
        return 'common';
    }
}
