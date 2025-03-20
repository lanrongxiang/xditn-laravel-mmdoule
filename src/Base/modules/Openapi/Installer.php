<?php

namespace Xditn\Base\modules\Openapi;

use Xditn\Base\modules\Openapi\Providers\OpenapiServiceProvider;
use Xditn\Support\Module\Installer as ModuleInstaller;

class Installer extends ModuleInstaller
{
    protected function info(): array
    {
        // TODO: Implement info() method.
        return [
            'title' => 'openapi',
            'name' => 'openapi',
            'path' => 'Openapi',
            'keywords' => 'openapi gateway',
            'description' => 'openapi 提供给外部接口',
            'provider' => OpenapiServiceProvider::class,
        ];
    }

    protected function requirePackages(): void
    {
        // TODO: Implement requirePackages() method.
    }

    protected function removePackages(): void
    {
        // TODO: Implement removePackages() method.
    }
}
