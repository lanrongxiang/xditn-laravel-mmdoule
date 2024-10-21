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
        $module = $this->argument('module');
        $name = $this->argument('name');
        $controllerPath = MModule::getModuleControllerPath($module);
        $file = $controllerPath.$this->getControllerFile($name);
        // 如果文件已存在，确认是否替换
        if (File::exists($file) && ! $this->confirmReplacement($file)) {
            return;
        }
        // 创建控制器文件
        $this->createControllerFile($file, $module, $name);
    }

    /**
     * 确认是否替换已存在的文件.
     *
     * @param  string  $file 文件路径
     * @return bool
     */
    protected function confirmReplacement(string $file): bool
    {
        return $this->ask("$file 已存在。是否替换?", 'Y') === 'Y';
    }

    /**
     * 创建控制器文件.
     *
     * @param  string  $file   文件路径
     * @param  string  $module 模块名称
     * @param  string  $name   控制器名称
     * @return void
     */
    protected function createControllerFile(string $file, string $module, string $name): void
    {
        File::put(
            $file,
            $this->getStubContent()->replace(['{namespace}', '{controller}'], [
                trim(MModule::getModuleControllerNamespace($module), '\\'),
                $this->getControllerName($name),
            ])->toString()
        );
        // 输出创建结果
        $this->info(File::exists($file) ? "$file 已创建." : "$file 创建失败.");
    }

    /**
     * 获取控制器文件名.
     *
     * @param  string  $name 控制器名称
     * @return string
     */
    protected function getControllerFile(string $name): string
    {
        return $this->getControllerName($name).'.php';
    }

    /**
     * 获取控制器名称.
     *
     * @param  string  $name 控制器名称
     * @return string
     */
    protected function getControllerName(string $name): string
    {
        // 如果名称不包含 'Controller'，则添加 'Controller' 后缀
        if (! Str::of($name)->contains('Controller')) {
            $name .= 'Controller';
        }

        return Str::of($name)->ucfirst()->toString();
    }

    /**
     * 获取存根内容.
     *
     * @return string
     */
    protected function getStubContent(): string
    {
        return File::get(dirname(__DIR__).DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'controller.stub');
    }
}
