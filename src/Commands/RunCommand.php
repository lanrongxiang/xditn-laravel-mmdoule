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

        //初始化迁移位置不要更改
        $this->info('正在运行 模块初始化命令...');
        $allModules =  getSubdirectories(base_path('modules'));
        foreach (sortArrayByPriorities(['system','permissions','user'],$allModules) as $name) {
            MModule::getModuleInstaller($name)->install();
        }

        app(Composer::class)->dumpAutoloads();
    }

}