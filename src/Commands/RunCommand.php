<?php

namespace Xditn\Commands;

use Illuminate\Console\Command;
use Xditn\MModule;
use Xditn\Support\Composer;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;

class RunCommand extends Command
{
    protected $signature = 'xditn:run {--timeout=300 : 设置迁移和种子操作的超时时间}';

    protected $description = 'xditn 系统初始化命令';

    public function handle(): void
    {
        $this->alert('开始执行 laravel 初始化命令...');

        // 设置更长的超时时间
        $timeout = (int)$this->option('timeout');
        set_time_limit($timeout);

        $this->callMigrate($timeout);
        $this->executePostCommands();
        $this->processModules();
        $this->updateEnvAndConfig();
        $this->callSeed($timeout);

        $this->alert('系统初始化成功!');
    }

    protected function callMigrate(int $timeout): void
    {
        $this->info('执行数据库迁移...');
        config(['database.migrations.timeout' => $timeout]);

        Artisan::call('migrate', [
            '--force' => true,
            '--step' => true
        ]);

        $this->info(Artisan::output());
    }

    protected function executePostCommands(): void
    {
        $this->info('执行发布命令...');

        $commands = [
            ['command' => 'key:generate', '--force' => true],
            ['command' => 'vendor:publish', '--tag' => 'xditn-config', '--force' => true],
            ['command' => 'vendor:publish', '--provider' => 'Laravel\Sanctum\SanctumServiceProvider', '--force' => true]
        ];

        $this->info('执行模块初始化命令...');
        foreach ($commands as $command) {
            Artisan::call($command['command'], array_except($command, 'command'));
            $this->info(Artisan::output());
        }
    }

    protected function processModules(): void
    {
        $this->info('处理模块安装...');

        $allModules = getSubdirectories(base_path('modules'));
        $this->withProgressBar(
            sortArrayByPriorities(['system', 'permissions', 'user'], $allModules),
            function ($name) {
                MModule::getModuleInstaller($name)->install();
            }
        );

        app(Composer::class)->dumpAutoloads();
    }

    protected function updateEnvAndConfig(): void
    {
        $this->info('更新环境配置...');

        $this->updateEnvFile();
        $this->info('清除配置缓存...');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
    }

    protected function callSeed(int $timeout): void
    {
        $this->info('执行数据库种子...');
        config(['database.seeds.timeout' => $timeout]);

        Artisan::call('db:seed', [
            '--force' => true,
            '--class' => 'Database\\Seeders\\DatabaseSeeder'
        ]);

        $this->info(Artisan::output());
    }

    private function updateEnvFile(): void
    {
        $envPath = app()->environmentFilePath();
        $content = File::get($envPath);
        $lines = explode("\n", $content);
        $existingKeys = [];

        $envUpdates = [
            'XDITN_MODULE_AUTOLOAD' => true,
            'BROADCAST_DRIVER'      => 'reverb',
            'REVERB_APP_ID'         => Str::random(7),
            'REVERB_APP_KEY'        => Str::random(20),
            'REVERB_APP_SECRET'     => Str::random(40),
            'REVERB_SCHEME'         => 'http',
            'REVERB_HOST'           => gethostbyname(gethostname()) ?? '127.0.0.1',
            'REVERB_PORT'           => '30008',
            'REVERB_SERVER_PORT'    => '30008',
        ];

        foreach ($lines as $i => $line) {
            [$key] = explode('=', $line);
            $key = trim($key);

            if (array_key_exists($key, $envUpdates)) {
                $lines[$i] = $this->updateEnvLine($line, $envUpdates[$key]);
                $existingKeys[$key] = true;
            }
        }

        foreach ($envUpdates as $key => $value) {
            if (!isset($existingKeys[$key])) {
                $lines[] = "{$key}={$value}";
            }
        }

        File::put($envPath, implode("\n", $lines));
        $this->reloadConfig();
    }

    private function updateEnvLine(string $line, $value): string
    {
        return preg_replace('/=.*$/', '='.$value, $line);
    }

    private function reloadConfig(): void
    {
        $app = app();
        $app->bootstrapWith([LoadEnvironmentVariables::class, LoadConfiguration::class]);
    }
}