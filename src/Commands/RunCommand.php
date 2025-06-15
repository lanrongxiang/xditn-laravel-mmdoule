<?php

namespace Xditn\Commands;

use Illuminate\Console\Application;
use Illuminate\Support\Facades\Process;
use Xditn\MModule;
use Xditn\Support\Composer;

class RunCommand extends XditnCommand
{
    protected $signature = 'xditn:run';

    protected $description = 'xditn 初始化运行';

    public function handle(): void
    {
        $this->info('正在运行 发布命令...');
        $commands = [
            'key:generate',
            'vendor:publish --tag=xditn-config',
            'vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"',
        ];
        foreach ($commands as $command) {
            Process::run(Application::formatCommandString($command))->throw();
        }

        $this->info('正在运行 模块初始化命令...');
        $allModules =  getSubdirectories(__DIR__.'/../Base/modules/');
        foreach ($allModules as $name) {
            MModule::getModuleInstaller($name)->uninstall();
            MModule::getModuleInstaller($name)->install();
        }
        $this->info('正在运行 laravel初始化命令...');
        //初始化迁移位置不要更改
        Process::run(Application::formatCommandString('migrate'))->throw();
        app(Composer::class)->dumpAutoloads();
    }

}