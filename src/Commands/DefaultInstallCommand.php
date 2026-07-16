<?php

namespace Xditn\Commands;

use Illuminate\Console\Command;
use Throwable;

class DefaultInstallCommand extends XditnCommand
{
    protected $signature = 'xditn:default:install {--force : 强制在生产环境执行迁移}';

    protected $description = '按配置顺序迁移并填充默认模块';

    public function handle(): int
    {
        $modules = collect(config('xditn.module.default', []))
            ->filter(fn ($module) => is_string($module) && $module !== '')
            ->map(fn (string $module) => strtolower($module))
            ->unique()
            ->values();

        if ($modules->isEmpty()) {
            $this->error('未配置默认模块，请检查 xditn.module.default。');

            return Command::FAILURE;
        }

        try {
            foreach ($modules as $module) {
                $this->info("正在迁移默认模块 [{$module}]...");
                $exitCode = $this->call('xditn:migrate', [
                    'module' => $module,
                    '--force' => (bool) $this->option('force'),
                ]);

                if ($exitCode !== Command::SUCCESS) {
                    $this->error("默认模块 [{$module}] 迁移失败。");

                    return Command::FAILURE;
                }
            }

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

        $this->info('默认模块迁移和数据填充执行完成。');

        return Command::SUCCESS;
    }
}
