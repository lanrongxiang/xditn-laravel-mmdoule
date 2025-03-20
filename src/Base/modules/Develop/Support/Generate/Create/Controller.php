<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Develop\Support\Generate\Create;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Xditn\MModule;

class Controller extends Creator
{
    protected array $replace = [
        '{namespace}', '{uses}', '{controller}', '{model}', '{request}',
    ];

    public function __construct(
        public readonly string $controller,
        public readonly string $model,
        public readonly ?string $request = null,
        public readonly bool $needForm = true
    ) {
    }

    /**
     * get file
     */
    public function getFile(): string
    {
        // TODO: Implement getFile() method.
        return MModule::getModuleControllerPath($this->module).$this->getControllerName().$this->ext;
    }

    public function getContent(): string|bool
    {
        // TODO: Implement getContent() method.
        return Str::of(File::get($this->getControllerStub()))->replace($this->replace, [
            $this->getControllerNamespace(),

            $this->getUses(),

            $this->getControllerName(),

            $this->model,

            $this->request ?: 'Request',
        ])->toString();
    }

    /**
     * get controller name
     */
    protected function getControllerName(): string
    {
        return Str::of($this->controller)->whenContains('Controller', function ($value) {
            return Str::of($value)->ucfirst();
        }, function ($value) {
            return Str::of($value)->append('Controller')->ucfirst();
        })->toString();
    }

    /**
     * get uses
     */
    protected function getUses(): string
    {
        return Str::of('use ')
            ->append(MModule::getModuleModelNamespace($this->module).$this->model)
            ->append(';')
            ->newLine()
            ->append('use ')
            ->when($this->request, function ($str) {
                return $str->append(MModule::getModuleRequestNamespace($this->module).$this->request);
            }, function ($str) {
                return $str->append("Illuminate\Http\Request");
            })->append(';')->newLine()->toString();
    }

    /**
     * get controller stub
     */
    protected function getControllerStub(): string
    {
        return dirname(__DIR__).DIRECTORY_SEPARATOR
                .'stubs'
                .DIRECTORY_SEPARATOR
                .($this->needForm ? 'controller.stub' : 'controllerIndex.stub');
    }

    /**
     * get controller namespace
     */
    protected function getControllerNamespace(): string
    {
        return Str::of(MModule::getModuleControllerNamespace($this->module))->rtrim('\\')->append(';')->toString();
    }
}
