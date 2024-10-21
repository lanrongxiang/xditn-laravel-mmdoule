<?php

namespace Modules\Permissions\Providers;

use Modules\Permissions\Middlewares\PermissionGate;
use Xditn\Providers\XditnModuleServiceProvider;

class PermissionsServiceProvider extends XditnModuleServiceProvider
{
    /**
     * middlewares
     *
     * @return string[]
     */
    protected function middlewares(): array
    {
       return [PermissionGate::class];
    }

    /**
     * route path
     *
     * @return string|array
     */
    public function moduleName(): string|array
    {
        // TODO: Implement path() method.
        return 'permissions';
    }
}
