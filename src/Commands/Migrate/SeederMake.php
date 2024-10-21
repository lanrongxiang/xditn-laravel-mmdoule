<?php

namespace Xditn\Commands\Migrate;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Xditn\Commands\XditnCommand;
use Xditn\MModule;

/**
 * 生成 Seeder 类命令
 */
class SeederMake extends XditnCommand
{
    /**
     * 命令名称和签名
     *
     * @var string
     */
    protected $signature = 'xditn:make:seeder {module : 模块名称} {name : Seeder 类名称}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '生成新的 Seeder 类';

    /**
     * 执行命令
     */
    public function handle(): void
    {
        $module = $this->argument('module');
        $name = $this->argument('name');

        $seederPath = MModule::getModuleSeederPath($module);

        if (! File::exists($seederPath)) {
            File::makeDirectory($seederPath, 0755, true);
        }

        $fileName = Str::studly($name).'Seeder.php';

        File::put($seederPath.'/'.$fileName, $this->buildSeeder($name));

        $this->info("Seeder {$fileName} 创建成功");
    }

    /**
     * 构建 Seeder 文件内容
     */
    protected function buildSeeder(string $name): string
    {
        $seederStub = File::get(__DIR__.'/stubs/seeder.stub');

        return str_replace(
            ['{{ seeder }}'],
            [Str::studly($name)],
            $seederStub
        );
    }
}
