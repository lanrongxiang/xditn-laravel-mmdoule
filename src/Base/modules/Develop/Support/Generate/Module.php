<?php

namespace Xditn\Modules\Develop\Support\Generate;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Xditn\MModule;

class Module
{
    public function __construct(
        public string $module,
        protected bool $controller,
        protected bool $models,
        protected bool $requests,
        protected bool $database
    ) {
    }

    /**
     * create
     *
     * @return void
     */
    public function create(): void
    {
        if ($this->controller) {
            MModule::getModuleControllerPath($this->module);
        }

        if ($this->models) {
            MModule::getModuleModelPath($this->module);
        }

        if ($this->requests) {
            MModule::getModuleRequestPath($this->module);
        }

        if ($this->database) {
            MModule::getModuleMigrationPath($this->module);
            MModule::getModuleSeederPath($this->module);
        }

        $this->createProvider();

        $this->createRoute();
    }


    /**
     * delete
     *
     * @return void
     */
    public function delete(): void
    {
    }

    /**
     * create provider
     *
     * @return void
     */
    protected function createProvider(): void
    {
        MModule::getModuleProviderPath($this->module);

        File::put(
            MModule::getModuleProviderPath($this->module).sprintf('%sServiceProvider.php', ucfirst($this->module)),
            Str::of(
                File::get(__DIR__.DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'provider.stub')
            )->replace(['{Module}', '{module}'], [ucfirst($this->module), $this->module])
        );
    }


    /**
     * create route
     *
     * @return void
     */
    protected function createRoute(): void
    {
        $content = Str::of(
            File::get(__DIR__.DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'route.stub')
        )->replace(['{module}'], [lcfirst($this->module)]);

        File::put(
            MModule::getModuleRoutePath($this->module),
            $content
        );
    }
}
