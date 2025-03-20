<?php

namespace Xditn\Base\modules\Shop;

use Xditn\Base\modules\Shop\Providers\ShopServiceProvider;
use Xditn\Support\Module\Installer as ModuleInstaller;

class Installer extends ModuleInstaller
{
    protected function info(): array
    {
        // TODO: Implement info() method.
        return [
            'title' => '商城管理',
            'name' => 'shop',
            'path' => 'Shop',
            'keywords' => '商城，管理',
            'description' => '这是一个商城管理模块',
            'provider' => ShopServiceProvider::class,
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

    protected function dependencies(): array
    {
        return ['member'];
    }
}
