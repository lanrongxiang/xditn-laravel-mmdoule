<?php

namespace Xditn\Commands;

use Illuminate\Console\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Xditn\MModule;
use Xditn\Support\Composer;

class RunCommand extends XditnCommand
{
    protected $signature = 'xditn:run';

    protected $description = 'xditn 初始化运行';

    public function handle(): void
    {
        $this->info('正在运行 laravel初始化命令...');
        Process::run(Application::formatCommandString('migrate'))->throw();
        $this->info('正在运行 发布命令...');
        $commands = [
            'key:generate',
            'vendor:publish --tag=xditn-config',
            'vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"',
        ];
        $this->info('正在运行 模块初始化命令...');
        foreach ($commands as $command) {
            Process::run(Application::formatCommandString($command))->throw();
        }
        $allModules = getSubdirectories(base_path('modules'));
        $this->info('正在运行 模块初迁移命令...');
        foreach (sortArrayByPriorities(['system', 'permissions', 'user'], $allModules) as $name) {
            MModule::getModuleInstaller($name)->install();
        }
        app(Composer::class)->dumpAutoloads();
        $this->info('正在更新.env文件 ...');
        $this->updateEnvFile();
        $this->info('正在运行 数据迁移命令...');
        Process::run(Application::formatCommandString('db:seed'))->throw();
        $this->info('系统初始化成功 ...');
    }

    private function updateEnvFile(): void
    {
        $envPath = app()->environmentFilePath();
        $content = File::get($envPath);

        // 动态生成随机凭证
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

        $lines = explode("\n", $content);
        $existingKeys = [];

        // 更新现有键值
        foreach ($lines as &$line) {
            foreach ($envUpdates as $key => $value) {
                if (str_contains($line, $key) && !str_contains($line, 'VITE_')) {
                    $line = $this->resetEnvValue($line, $value);
                    $existingKeys[$key] = true;
                }
            }
        }

        // 添加缺失的键值
        foreach ($envUpdates as $key => $value) {
            if (!isset($existingKeys[$key])) {
                $lines[] = "{$key}={$value}";
            }
        }

        File::put($envPath, implode("\n", $lines));
        app()->bootstrapWith([LoadEnvironmentVariables::class, LoadConfiguration::class]);
    }

    /**
     * 重置环境变量值
     *
     * @param string $originValue
     * @param string $newValue
     *
     * @return string
     */
    protected function resetEnvValue(string $originValue, string $newValue): string
    {
        if (Str::contains($originValue, '=')) {
            $originValue    = explode('=', $originValue);
            $originValue[1] = $newValue;
            return implode('=', $originValue);
        }
        return $originValue;
    }

}