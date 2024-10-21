<?php

namespace Modules\User;

use Xditn\Support\Module\Installer as ModuleInstaller;

class Installer extends ModuleInstaller
{
    protected function info(): array
    {
        return [
            'title'       => '用户管理模块',
            'name'        => 'user',
            'path'        => 'user',
            'keywords'    => '用户管理, system',
            'description' => '用户管理模块'
        ];
    }

    protected function requirePackages(): void
    {
        //
    }

    protected function removePackages(): void
    {
        //
    }

    public function install(): void
    {
        $this->moduleRepository->create($this->info());
    }
}
