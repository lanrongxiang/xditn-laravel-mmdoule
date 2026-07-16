<?php

namespace Xditn\Commands\Migrate;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;
use Xditn\Commands\XditnCommand;
use Xditn\MModule;

/**
 * 执行数据库迁移命令
 */
class MigrateRun extends XditnCommand
{
    protected $signature = 'xditn:migrate {module : 模块名称} {--force}';

    protected $description = '执行模块的数据库迁移';

    public function handle(): int
    {
        $module = (string) $this->argument('module');
        $modulePath = MModule::getModuleMigrationPath($module);

        if (! File::isDirectory($modulePath)) {
            $this->warn("模块 [{$module}] 没有 migrations 目录，已跳过。");

            return Command::SUCCESS;
        }

        $files = File::files($modulePath);
        if ($files === []) {
            $this->warn("模块 [{$module}] 没有迁移文件，已跳过。");

            return Command::SUCCESS;
        }

        $this->info("开始迁移模块 [{$module}]，共 ".count($files).' 个文件...');

        try {
            foreach ($files as $file) {
                $path = Str::of(MModule::getModuleRelativePath($modulePath))
                    ->remove('.')
                    ->append($file->getFilename())
                    ->toString();

                // 使用 $this->call 才能把 migrate 进度输出到当前终端
                $exitCode = $this->call('migrate', [
                    '--path' => $path,
                    '--force' => (bool) $this->option('force'),
                ]);

                if ($exitCode !== Command::SUCCESS) {
                    $this->error("模块 [{$module}] 迁移失败: {$file->getFilename()}");

                    return Command::FAILURE;
                }
            }
        } catch (Throwable $e) {
            $this->error("模块 [{$module}] 迁移异常: ".$e->getMessage());

            return Command::FAILURE;
        }

        $this->info("模块 [{$module}] 迁移完成。");

        return Command::SUCCESS;
    }
}
