<?php

namespace Xditn\Commands\Migrate;

use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Throwable;
use Xditn\Commands\XditnCommand;
use Xditn\MModule;

/**
 * 运行数据库填充命令
 */
class SeedRun extends XditnCommand
{
    protected $signature = 'xditn:db:seed {module : 模块名称} {--seeder= : 指定 Seeder 类}';

    protected $description = '执行模块数据库填充';

    public function handle(): int
    {
        $module = (string) $this->argument('module');
        $seederName = $this->option('seeder');
        $seederPath = MModule::getModuleSeederPath($module);

        if (! File::isDirectory($seederPath)) {
            $this->warn("模块 [{$module}] 没有 seeder 目录，已跳过。");

            return Command::SUCCESS;
        }

        $files = File::allFiles($seederPath);
        if ($files === []) {
            $this->warn("模块 [{$module}] 没有 Seeder 文件，已跳过。");

            return Command::SUCCESS;
        }

        $this->info("开始填充模块 [{$module}]...");

        try {
            foreach ($files as $file) {
                $className = pathinfo($file->getBasename(), PATHINFO_FILENAME);
                if ($seederName && $className !== $seederName) {
                    continue;
                }

                $seeder = require $file->getRealPath();

                if ($seeder instanceof Seeder) {
                    $this->line('  -> '.$file->getFilename().' (匿名 Seeder)');
                    $seeder->setContainer(app())->setCommand($this)([]);

                    continue;
                }

                $className = $this->resolveSeederClass($file->getRealPath(), $className);
                if (! class_exists($className)) {
                    $this->error("无法加载 Seeder: {$file->getFilename()}，文件必须返回 Seeder 实例或声明 Seeder 类。");

                    return Command::FAILURE;
                }

                $seeder = app()->make($className);
                if (! $seeder instanceof Seeder) {
                    $this->error("无效 Seeder: {$className} 未继承 ".Seeder::class);

                    return Command::FAILURE;
                }

                $this->line("  -> {$className}");
                $seeder->setContainer(app())->setCommand($this)([]);
            }
        } catch (Throwable $e) {
            $this->error("模块 [{$module}] 填充异常: ".$e->getMessage());

            return Command::FAILURE;
        }

        $this->info("模块 [{$module}] 填充完成。");

        return Command::SUCCESS;
    }

    protected function resolveSeederClass(string $path, string $fallback): string
    {
        $content = File::get($path);
        if (preg_match('/namespace\s+([^;]+);/', $content, $ns)
            && preg_match('/class\s+(\w+)/', $content, $cls)) {
            return trim($ns[1]).'\\'.$cls[1];
        }

        return $fallback;
    }
}
