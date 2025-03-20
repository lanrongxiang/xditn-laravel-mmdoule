<?php

namespace Xditn\Commands\Create;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Xditn\Commands\XditnCommand;
use Xditn\MModule;

/**
 * 控制器生成命令
 */
class Controller extends XditnCommand
{
    /**
     * 控制台命令的名称和签名.
     *
     * @var string
     */
    protected $signature = 'xditn:make:controller {module} {name}';

    /**
     * 控制台命令的描述.
     *
     * @var string
     */
    protected $description = '创建控制器';

    /**
     * 执行控制台命令.
     *
     * @return void
     */
    public function handle(): void
    {
        $controllerPath = MModule::getModuleControllerPath($this->argument('module'));
        $file = $controllerPath.$this->getControllerFile();
        if (File::exists($file) && ! $this->confirmOverwrite($file)) {
            exit;
        }
        File::put($file, $this->generateStubContent());
        $this->info(File::exists($file) ? "$file 已创建" : "$file 创建失败");
    }

    /**
     * 确认是否覆盖文件
     */
    protected function confirmOverwrite(string $file): bool
    {
        return $this->ask("$file 已存在，是否替换？", 'Y') === 'Y';
    }

    /**
     * 获取控制器文件名
     */
    protected function getControllerFile(): string
    {
        return $this->getControllerName().'.php';
    }

    /**
     * 获取控制器名称
     */
    protected function getControllerName(): string
    {
        return Str::of($this->argument('name'))->whenContains(
                'Controller',
                fn ($str) => $str,
                fn ($str) => $str->append('Controller')
            )->ucfirst()->toString();
    }

    /**
     * 生成模板内容
     */
    protected function generateStubContent(): string
    {
        $namespace = trim(MModule::getModuleControllerNamespace($this->argument('module')), '\\');

        return Str::of(
            File::get(dirname(__DIR__).DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'controller.stub')
        )->replace(['{namespace}', '{controller}'], [$namespace, $this->getControllerName()])->toString();
    }
}
