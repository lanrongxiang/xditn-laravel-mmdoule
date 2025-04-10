<?php

namespace Xditn\Commands\Migrate;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Xditn\Commands\XditnCommand;
use Xditn\MModule;

/**
 * 生成数据库迁移命令
 */
class MigrateMake extends XditnCommand
{
    /**
     * 命令名称和签名
     *
     * @var string
     */
    protected $signature = 'xditn:make:migration {module : 模块名称} {name : 迁移文件名称} {table : 数据库表名}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '生成新的迁移文件';

    /**
     * 执行命令
     */
    public function handle(): void
    {
        $module = $this->argument('module');
        $name = $this->argument('name');
        $table = $this->argument('table');

        $migrationPath = MModule::getModuleMigrationPath($module);

        if (! File::exists($migrationPath)) {
            File::makeDirectory($migrationPath, 0755, true);
        }

        $fileName = date('Y_m_d_His').'_'.Str::snake($name).'.php';

        File::put($migrationPath.'/'.$fileName, $this->buildMigration($module, $name,$table));

        $this->info("迁移文件 {$fileName} 创建成功");
    }

    /**
     * 构建迁移文件内容
     */
    protected function buildMigration(string $module, string $name, string $table): string
    {
        $migrationStub = File::get(__DIR__.'/../stubs/migration.stub');

        return str_replace(
            ['{{ module }}', '{{ migration }}', '{{ table }}'],
            [$module, Str::studly($name) , $table],
            $migrationStub
        );
    }
}
