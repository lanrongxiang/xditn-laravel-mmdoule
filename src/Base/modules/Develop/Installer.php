<?php

namespace Xditn\Base\modules\Develop;

use Xditn\Base\modules\Develop\Providers\DevelopServiceProvider;
use Xditn\Support\Module\Installer as ModuleInstaller;

/**
 * install
 */
class Installer extends ModuleInstaller
{
    protected function info(): array
    {
        //此模块建议不要使用安装 这个只能在本地环境使用
        return [
            'title'       => '开发工具',
            'name'        => 'develop',
            'path'        => 'Develop',
            'keywords'    => 'develop module generator',
            'description' => '模块管理 数据表管理',
            'provider'    => DevelopServiceProvider::class,
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
