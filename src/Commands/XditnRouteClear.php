<?php

declare(strict_types=1);

namespace Xditn\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Xditn\MModule;

class XditnRouteClear extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = 'xditn:route:clear';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '清除路由缓存';

    /**
     * 文件系统实例
     *
     * @var Filesystem
     */
    protected Filesystem $files;

    /**
     * 构造函数，初始化文件系统实例
     *
     * @param  Filesystem  $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * 执行命令的主要逻辑
     *
     * @return void
     */
    public function handle(): void
    {
        // 删除应用路由缓存
        $this->deleteCache($this->laravel->getCachedRoutesPath());

        // 删除管理路由缓存
        $this->deleteCache(MModule::getRouteCachePath());
    }

    /**
     * 删除指定路径的缓存文件
     *
     * @param  string  $path
     * @return void
     */
    protected function deleteCache(string $path): void
    {
        if ($this->files->exists($path)) { // 检查文件是否存在
            $this->files->delete($path); // 删除缓存文件
            $this->components->info("缓存文件 {$path} 已成功删除。");
        } else {
            $this->components->warn("缓存文件 {$path} 不存在。");
        }
    }
}
