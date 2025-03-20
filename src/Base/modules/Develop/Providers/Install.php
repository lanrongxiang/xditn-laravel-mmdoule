<?php

namespace Xditn\Base\modules\Develop\Providers;

use Xditn\Support\Module\Installer;

/**
 * install
 */
class Install extends Installer
{
    protected function info(): array
    {
        return [
            'title' => '开发工具',
            'name' => 'develop',
            'path' => 'Develop',
            'keywords' => 'develop module generator',
            'description' => '模块管理 数据表管理',
            'provider' => DevelopServiceProvider::class,
        ];
    }

    protected function migration(): string
    {
        // TODO: Implement migration() method.
        return '';
    }

    protected function seeder(): string
    {
        // TODO: Implement seed() method.
        return '';
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
