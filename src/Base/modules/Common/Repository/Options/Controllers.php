<?php

namespace Xditn\Base\modules\Common\Repository\Options;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Xditn\MModule;

class Controllers implements OptionInterface
{
    public function get(): array|Collection
    {
        $controllers = [];

        if ($module = request()->get('module')) {
            $controllerFiles = File::glob(MModule::getModuleControllerPath($module).'*.php');

            foreach ($controllerFiles as $controllerFile) {
                $controllers[] = [
                    'label' => Str::of(File::name($controllerFile))->lcfirst()->remove('Controller'),

                    'value' => Str::of(File::name($controllerFile))->lcfirst()->remove('Controller'),
                ];
            }
        }

        return $controllers;
    }
}
