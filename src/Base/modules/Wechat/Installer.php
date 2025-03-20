<?php

namespace Xditn\Base\modules\Wechat;

use Xditn\Base\modules\Wechat\Providers\WechatServiceProvider;
use Xditn\Support\Module\Installer as ModuleInstaller;

class Installer extends ModuleInstaller
{
    protected function info(): array
    {
        // TODO: Implement info() method.
        return [
            'title' => '微信管理',
            'name' => 'wechat',
            'path' => 'wechat',
            'keywords' => '微信管理, wechat',
            'description' => '微信管理模块',
            'provider' => WechatServiceProvider::class,
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
