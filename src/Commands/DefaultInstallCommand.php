<?php

namespace Xditn\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

class DefaultInstallCommand extends XditnCommand
{
    protected $signature = 'xditn:default:install {--force : 强制在生产环境执行迁移}';

    protected $description = '先迁移宿主项目，再按配置顺序迁移并填充默认模块';

    protected array $legacyModuleMigrations = [
        '2022_11_14_034127_module.php',
    ];

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $migrated = [];
        $seeded = [];

        try {
            if (! $this->publishHostModuleMigration()) {
                return Command::FAILURE;
            }

            $this->info('正在迁移宿主项目 database/migrations...');
            $exitCode = $this->call('migrate', [
                '--force' => $force,
            ]);

            if ($exitCode !== Command::SUCCESS) {
                $this->error('宿主项目迁移失败。');

                return Command::FAILURE;
            }
            $this->info('宿主项目迁移完成。');

            $modules = collect(config('xditn.module.default', []))
                ->filter(fn ($module) => is_string($module) && $module !== '')
                ->map(fn (string $module) => strtolower($module))
                ->unique()
                ->values();

            if ($modules->isEmpty()) {
                $this->warn('未配置默认模块（xditn.module.default），已跳过模块迁移与填充。');
                $this->info('安装流程结束。');

                return Command::SUCCESS;
            }

            $this->info('待处理默认模块: '.$modules->implode(', '));

            foreach ($modules as $module) {
                $this->newLine();
                $this->info("[{$module}] 正在迁移...");
                $exitCode = $this->call('xditn:migrate', [
                    'module' => $module,
                    '--force' => $force,
                ]);

                if ($exitCode !== Command::SUCCESS) {
                    $this->error("[{$module}] 迁移失败，已中止。");

                    return Command::FAILURE;
                }

                $migrated[] = $module;
                $this->info("[{$module}] 迁移成功。");
            }

            foreach ($modules as $module) {
                $this->newLine();
                $this->info("[{$module}] 正在填充数据...");
                $exitCode = $this->call('xditn:db:seed', [
                    'module' => $module,
                ]);

                if ($exitCode !== Command::SUCCESS) {
                    $this->error("[{$module}] 数据填充失败，已中止。");

                    return Command::FAILURE;
                }

                $seeded[] = $module;
                $this->info("[{$module}] 填充成功。");
            }
        } catch (Throwable $e) {
            $this->newLine();
            $this->error('安装中断: '.$e->getMessage());
            if ($migrated !== []) {
                $this->warn('已完成迁移的模块: '.implode(', ', $migrated));
            }
            if ($seeded !== []) {
                $this->warn('已完成填充的模块: '.implode(', ', $seeded));
            }

            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('========================================');
        $this->info('全部完成');
        $this->info('已迁移模块: '.($migrated ? implode(', ', $migrated) : '(无)'));
        $this->info('已填充模块: '.($seeded ? implode(', ', $seeded) : '(无)'));
        $this->info('========================================');

        return Command::SUCCESS;
    }

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
