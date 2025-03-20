<?php

namespace Xditn\Commands\Migrate;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Xditn\Commands\XditnCommand;
use Xditn\MModule;

/**
 * 刷新数据库迁移命令
 */
class MigrateFresh extends XditnCommand
{
    /**
     * 命令名称和签名
     *
     * @var string
     */
    protected $signature = 'xditn:migrate:fresh {module} {--force}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '刷新指定模块的数据库迁移';

    /**
     * 执行命令
     */
    public function handle(): void
    {
        $module = $this->argument('module');

        if (! File::isDirectory(MModule::getModuleMigrationPath($module))) {
            Artisan::call('migrate:fresh', [
                '--path' => MModule::getModuleMigrationPath($module),
                '--force' => $this->option('force'),
            ]);
        }

        $this->info('数据库迁移已刷新');
    }
}
