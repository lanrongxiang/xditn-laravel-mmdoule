<?php

namespace Xditn\Base\modules\Permissions\Providers;

use Xditn\Base\modules\Permissions\Middlewares\PermissionGate;
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
     */
    public function moduleName(): string|array
    {
        // TODO: Implement path() method.
        return 'permissions';
    }
}
