<?php

namespace Xditn\Base\modules\System;

use Xditn\Base\modules\System\Providers\SystemServiceProvider;
use Xditn\Support\Module\Installer as ModuleInstaller;

class Installer extends ModuleInstaller
{
    protected function info(): array
    {
        // TODO: Implement info() method.
        return [
            'title' => '系统管理',
            'name' => 'system',
            'path' => 'System',
            'keywords' => '系统管理',
            'description' => '系统管理模块',
            'provider' => SystemServiceProvider::class,
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
