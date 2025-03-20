<?php

namespace Xditn\Base\modules\Openapi\Providers;

use Xditn\Base\modules\Openapi\Support\OpenapiAuth;
use Xditn\Providers\XditnModuleServiceProvider;

class OpenapiServiceProvider extends XditnModuleServiceProvider
{
    /**
     * route path
     */
    public function moduleName(): string
    {
        // TODO: Implement path() method.
        return 'openapi';
    }

    public function boot(): void
    {
        $this->app->singleton(OpenapiAuth::class, fn () => new OpenapiAuth);
    }
}
