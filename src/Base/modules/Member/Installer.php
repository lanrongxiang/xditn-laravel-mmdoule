<?php

namespace Xditn\Base\modules\Member;

use Xditn\Base\modules\Member\Providers\MemberServiceProvider;
use Xditn\Support\Module\Installer as ModuleInstaller;

class Installer extends ModuleInstaller
{
    protected function info(): array
    {
        // TODO: Implement info() method.
        return [
            'title' => '会员管理',
            'name' => 'member',
            'path' => 'member',
            'keywords' => '会员管理',
            'description' => '会员管理模块',
            'provider' => MemberServiceProvider::class,
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
