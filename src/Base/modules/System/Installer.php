<?php

namespace Modules\System;

use Xditn\Support\Module\Installer as ModuleInstaller;

class Installer extends ModuleInstaller
{
    protected function info(): array
    {
        // TODO: Implement info() method.
        return [
            'title' => '系统管理',
            'name' => 'system',
            'path' => 'system',
            'keywords' => '系统管理, system',
            'description' => '系统管理模块'
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
