<?php

namespace Xditn\Base\modules\User;

use Xditn\Base\modules\User\Providers\UserServiceProvider;
use Xditn\Support\Module\Installer as ModuleInstaller;

class Installer extends ModuleInstaller
{
    protected function info(): array
    {
        // TODO: Implement info() method.
        return [
            'title' => '用户管理',
            'name' => 'user',
            'path' => 'user',
            'keywords' => '用户管理，用户管理模块',
            'description' => '用户管理模块',
            'provider' => UserServiceProvider::class,
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
