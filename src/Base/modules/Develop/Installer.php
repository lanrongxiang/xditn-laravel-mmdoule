<?php

namespace Xditn\Modules\Develop;

use Xditn\Support\Module\Installer as ModuleInstaller;

class Installer extends ModuleInstaller
{
    protected function info(): array
    {
        return [
            'title'       => '模块&字段管理',
            'name'        => 'develop',
            'path'        => 'develop',
            'keywords'    => '模块, 字段',
            'description' => '模块&字段管理',
            "enable"      => false,
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
