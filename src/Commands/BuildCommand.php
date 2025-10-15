<?php

namespace Xditn\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class BuildCommand extends XditnCommand
{

    protected $signature = 'xditn:build {--no-check}';

    protected $description = '打包后台应用';

    public function handle(): void
    {
        try {
            Artisan::call('schema:dump');

            $schemaPath = $this->getDumpSchemaPath();

            if (!File::exists($schemaPath)) {
                throw new \Exception('Schema 导出失败，文件未生成');
            }

            File::copy($schemaPath, $this->getImportPath());

            $this->info('✅ 后台打包完成，SQL 文件已生成');

        } catch (\Exception $e) {
            $this->error("打包失败: " . $e->getMessage());
        }
    }

    protected function getDumpSchemaPath(): string
    {
        return database_path('schema/mysql-schema.sql');
    }

    protected function getImportPath(): string
    {
        return base_path('import.sql');
    }
}
