<?php

namespace Xditn\Support\Module;

use Illuminate\Console\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Xditn\Contracts\ModuleRepositoryInterface;
use Xditn\Support\Composer;

/**
 * Installer 抽象类，用于安装、卸载模块，并处理迁移、数据填充和依赖包的管理
 */
abstract class Installer
{
    /**
     * 构造函数，初始化模块仓库接口
     *
     * @param  ModuleRepositoryInterface  $moduleRepository 模块仓库接口
     */
    public function __construct(protected ModuleRepositoryInterface $moduleRepository)
    {
    }

    /**
     * 获取模块信息
     *
     * @return array 模块信息数组
     */
    abstract protected function info(): array;

    public function getInfo(): array
    {
        return $this->info();
    }

    /**
     * 执行迁移操作
     *
     * @return void
     */
    protected function migrate(): void
    {
        $name = Str::ucfirst(Str::lower($this->info()['name']));
        $command = Application::formatCommandString('xditn:migrate '.$name);
        app()->runningInConsole()
            ? Process::run($command)->throw()
            : Artisan::call('xditn:migrate', ['module' => $name]);
    }

    /**
     * 执行数据填充操作
     *
     * @return void
     */
    protected function seed(): void
    {
        $name = Str::ucfirst(Str::lower($this->info()['name']));
        $command = Application::formatCommandString('xditn:db:seed '.$name);
        app()->runningInConsole()
            ? Process::run($command)->throw()
            : Artisan::call('xditn:db:seed', ['module' => $name]);
    }

    /**
     * 安装所需的依赖包
     *
     * @return void
     */
    abstract protected function requirePackages(): void;

    /**
     * 移除不需要的依赖包
     *
     * @return void
     */
    abstract protected function removePackages(): void;

    /**
     * 卸载模块
     *
     * @return void
     */
    public function uninstall(): void
    {
        $name = Str::ucfirst(Str::lower($this->info()['name']));
        $this->moduleRepository->delete($name);
        $this->removePackages();
    }

    /**
     * 安装模块
     *
     * @return void
     */
    public function install(): void
    {
        $this->migrate();
        $this->seed();
        $this->requirePackages();
    }


    /**
     * 获取 Composer 实例
     *
     * @return Composer
     */
    protected function composer(): Composer
    {
        return app(Composer::class);
    }
}
