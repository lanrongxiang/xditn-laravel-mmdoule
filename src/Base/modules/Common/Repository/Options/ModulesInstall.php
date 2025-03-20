<?php

namespace Xditn\Base\modules\Common\Repository\Options;

use Xditn\MModule;
use Xditn\Support\Module\ModuleRepository;

class ModulesInstall implements OptionInterface
{
    public function get(): array
    {
        $modules = [];
        $enabledModuleNames = app(ModuleRepository::class)->getEnabled()->pluck('name')->merge(
            config('xditn.module.default', [])
        );
        foreach (MModule::getModulesPath() as $module) {
            try {
                $installer = MModule::getModuleInstaller(basename($module));
                $info      = $installer->getInfo();
                if (!$enabledModuleNames->contains($info['name'])) {
                    $modules[] = [
                        'label' => $info['title'],
                        'value' => $info['name'],
                    ];
                }
            } catch (\Exception $e) {
            }
        }
        return $modules;
    }
}
