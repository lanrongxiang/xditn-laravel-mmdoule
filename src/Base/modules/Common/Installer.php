<?php

namespace Xditn\Base\modules\Common;

use Xditn\Base\modules\Common\Providers\CommonServiceProvider;
use Xditn\Support\Module\Installer as ModuleInstaller;

class Installer extends ModuleInstaller
{
    protected function info(): array
    {
        // TODO: Implement info() method.
        return [
            'title'       => '公共模块',
            'name'        => 'common',
            'path'        => 'Common',
            'keywords'    => '',
            'description' => '',
            'provider'    => CommonServiceProvider::class,
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
}
