<?php

namespace Xditn\Support\Module;

use Illuminate\Console\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use Xditn\Contracts\ModuleRepositoryInterface;
use Xditn\Support\Composer;

/**
 * 模块安装器抽象类
 */
abstract class Installer
{
    public function __construct(protected ModuleRepositoryInterface $moduleRepository)
    {
    }

    /**
     * 获取模块信息
     *
     * @return array
     */
    abstract protected function info(): array;

    /**
     * 执行迁移命令
     *
     * @param  string  $command
     * @return void
     */
    protected function runCommand(string $command): void
    {
        $name = $this->info()['name'];

        if (app()->runningInConsole()) {
            Process::run(Application::formatCommandString("xditn:$command $name"))->throw();
        } else {
            Artisan::call("xditn:$command", ['module' => $name]);
        }
    }

    /**
     * 迁移数据库
     */
    protected function migrate(): void
    {
        $this->runCommand('migrate');
    }

    /**
     * 执行种子
     */
    protected function seed(): void
    {
        $this->runCommand('db:seed');
    }

    /**
     * 安装依赖包
     */
    abstract protected function requirePackages(): void;

    /**
     * 删除依赖包
     */
    abstract protected function removePackages(): void;

    /**
     * 卸载模块
     */
    public function uninstall(): void
    {
        $this->moduleRepository->delete($this->info()['name']);
        $this->removePackages();
    }

    /**
     * 安装模块
     */
    public function install(): void
    {
        $this->moduleRepository->create($this->info());
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
