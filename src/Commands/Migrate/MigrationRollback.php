<?php

namespace Xditn\Commands\Migrate;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Xditn\Commands\XditnCommand;
use Xditn\MModule;

/**
 * 数据库迁移回滚命令
 */
class MigrationRollback extends XditnCommand
{
    /**
     * 命令名称和签名
     *
     * @var string
     */
    protected $signature = 'xditn:migrate:rollback {module : 模块名称} {--force}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '回滚模块的数据库迁移';

    /**
     * 执行命令
     */
    public function handle(): void
    {
        $module = $this->argument('module');

        if (File::isDirectory(MModule::getModuleMigrationPath($module))) {
            Artisan::call('migrate:rollback', [
                '--path' => MModule::getModuleMigrationPath($module),
                '--force' => $this->option('force'),
            ]);

            $this->info('数据库迁移已回滚');
        } else {
            $this->error("模块 {$module} 没有可用的迁移文件");
        }
    }
}
