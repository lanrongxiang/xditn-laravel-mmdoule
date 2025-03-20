<?php

namespace Xditn\Base\modules\Cms;

use Xditn\Base\modules\Cms\Providers\CmsServiceProvider;
use Xditn\Support\Module\Installer as ModuleInstaller;

class Installer extends ModuleInstaller
{
    protected function info(): array
    {
        // TODO: Implement info() method.
        return [
            'title' => '内容管理',
            'name' => 'cms',
            'path' => 'cms',
            'keywords' => '内容管理, cms',
            'description' => '内容管理模块',
            'provider' => CmsServiceProvider::class,
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
