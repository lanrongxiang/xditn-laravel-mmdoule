<?php

namespace Xditn\Commands\Migrate;

use Illuminate\Support\Facades\File;
use Xditn\Commands\XditnCommand;
use Xditn\MModule;

/**
 * 运行数据库填充命令
 */
class SeedRun extends XditnCommand
{
    /**
     * 命令名称和签名
     *
     * @var string
     */
    protected $signature = 'xditn:db:seed {module : 模块名称} {--seeder= : 指定 Seeder 类}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '执行模块数据库填充';

    /**
     * 执行控制台命令.
     *
     * @return void
     */
    public function handle(): void
    {
        $module = $this->argument('module');
        $seederName = $this->option('seeder');
        $files = File::allFiles(MModule::getModuleSeederPath($module));
        // 根据 seeder 名称选择相应的填充类并执行
        foreach ($files as $file) {
            $className = pathinfo($file->getBasename(), PATHINFO_FILENAME);
            if ($seederName && $className !== $seederName) {
                continue; // 如果指定了 seeder 名称且不匹配，跳过
            }

            // 载入填充类并执行
            $class = require_once $file->getRealPath();
            (new $class())->run(); // 创建实例并执行 run 方法
        }
    }
}
