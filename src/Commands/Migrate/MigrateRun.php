<?php

namespace Xditn\Commands\Migrate;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Xditn\Commands\XditnCommand;
use Xditn\MModule;

/**
 * 执行数据库迁移命令
 */
class MigrateRun extends XditnCommand
{
    /**
     * 命令名称和签名
     *
     * @var string
     */
    protected $signature = 'xditn:migrate {module : 模块名称} {--force}';

    protected $description = '执行模块的数据库迁移';

    public function handle(): void
    {
        $modulePath = MModule::getModuleMigrationPath($this->argument('module'));
        if (File::isDirectory($modulePath)) {
            foreach (File::files($modulePath) as $file) {
                $path = Str::of(MModule::getModuleRelativePath($modulePath))
                           ->remove('.')->append($file->getFilename());
                Artisan::call('migrate', [
                    '--path' => $path,
                    '--force' => $this->option('force'),
                ]);
            }

            $this->info("Module [{$this->argument('module')}] migrated successfully.");
        } else {
            $this->error('No migration files in module.');
        }
    }
}
