<?php

namespace Xditn\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

class DefaultInstallCommand extends XditnCommand
{
    protected $signature = 'xditn:default:install {--force : 强制在生产环境执行迁移}';

    protected $description = '先迁移宿主项目，再按配置顺序迁移并填充默认模块';

    /**
     * 历史错误命名的模块表迁移，发布新文件后需清理，避免重复建表。
     */
    protected array $legacyModuleMigrations = [
        '2022_11_14_034127_module.php',
    ];

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        try {
            // 1. 强制发布脚手架模块表迁移到宿主 database/migrations
            if (! $this->publishHostModuleMigration()) {
                return Command::FAILURE;
            }

            // 2. 先跑宿主项目迁移（含 admin_modules 等），避免模块迁移依赖冲突
            $this->info('正在迁移宿主项目 database/migrations...');
            $exitCode = $this->call('migrate', [
                '--force' => $force,
            ]);

            if ($exitCode !== Command::SUCCESS) {
                $this->error('宿主项目迁移失败。');

                return Command::FAILURE;
            }

            $modules = collect(config('xditn.module.default', []))
                ->filter(fn ($module) => is_string($module) && $module !== '')
                ->map(fn (string $module) => strtolower($module))
                ->unique()
                ->values();

            if ($modules->isEmpty()) {
                $this->warn('未配置默认模块（xditn.module.default），已跳过模块迁移与填充。');

                return Command::SUCCESS;
            }

            // 3. 再迁移默认模块
            foreach ($modules as $module) {
                $this->info("正在迁移默认模块 [{$module}]...");
                $exitCode = $this->call('xditn:migrate', [
                    'module' => $module,
                    '--force' => $force,
                ]);

                if ($exitCode !== Command::SUCCESS) {
                    $this->error("默认模块 [{$module}] 迁移失败。");

                    return Command::FAILURE;
                }
            }

            // 4. 填充默认模块数据
            foreach ($modules as $module) {
                $this->info("正在填充默认模块 [{$module}]...");
                $exitCode = $this->call('xditn:db:seed', [
                    'module' => $module,
                ]);

                if ($exitCode !== Command::SUCCESS) {
                    $this->error("默认模块 [{$module}] 数据填充失败。");

                    return Command::FAILURE;
                }
            }
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return Command::FAILURE;
        }

        $this->info('宿主迁移与默认模块安装执行完成。');

        return Command::SUCCESS;
    }

    /**
     * 将包内模块表迁移强制同步到宿主项目，并清理旧命名文件。
     */
    protected function publishHostModuleMigration(): bool
    {
        $this->info('正在同步 xditn 模块表迁移到宿主项目...');

        $exitCode = $this->call('vendor:publish', [
            '--tag' => 'xditn-module',
            '--force' => true,
        ]);

        if ($exitCode !== Command::SUCCESS) {
            $this->error('发布模块表迁移失败。');

            return false;
        }

        $this->removeLegacyModuleMigrations();

        $target = database_path('migrations/2024_10_25_034127_module.php');
        if (! File::exists($target)) {
            $this->error("未找到已发布的迁移文件: {$target}");

            return false;
        }

        $this->info('模块表迁移已更新: 2024_10_25_034127_module.php');

        return true;
    }

    /**
     * 删除旧命名迁移，防止与新文件同时 create 同一张表。
     */
    protected function removeLegacyModuleMigrations(): void
    {
        foreach ($this->legacyModuleMigrations as $filename) {
            $path = database_path('migrations/'.$filename);
            if (File::exists($path)) {
                File::delete($path);
                $this->warn("已移除冲突的旧迁移文件: {$filename}");
            }
        }
    }
}
