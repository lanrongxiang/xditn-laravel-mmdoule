<?php

namespace Xditn\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class BuildCommand extends XditnCommand
{

    protected $signature = 'xditn:build {--no-check}';

    protected $description = '打包后台';

    public function handle(): void
    {
        Artisan::call('schema:dump');

        if (file_exists($schemaSql = $this->getDumpSchemaSql())) {
            File::put(
                $this->btImportSql(),
                file_get_contents($schemaSql)
            );
        }
    }

    protected function getDumpSchemaSql(): string
    {
        return database_path('schema') . DIRECTORY_SEPARATOR . 'mysql-schema.sql';
    }

    protected function btImportSql(): string
    {
        return base_path() . DIRECTORY_SEPARATOR . 'import.sql';
    }
}