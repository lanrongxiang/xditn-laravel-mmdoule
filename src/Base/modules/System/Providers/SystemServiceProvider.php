<?php

namespace Modules\System\Providers;


use Xditn\Providers\XditnModuleServiceProvider;

class SystemServiceProvider extends XditnModuleServiceProvider
{
    /**
     * route path
     *
     * @return string
     */
    public function moduleName(): string
    {
        // TODO: Implement path() method.
        return 'system';
    }
}
