<?php

namespace Xditn\Commands;

use Illuminate\Console\Application;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use function Laravel\Prompts\text;
use Throwable;
use Xditn\Exceptions\FailedException;
use Xditn\MModule;
use Xditn\Support\Composer;

/**
 * @note 模块表迁移发布后需清理历史错误命名文件，避免与 2024_10_25_034127_module 冲突
 */

class InstallCommand extends XditnCommand
{
    protected $signature = 'xditn:install {--prod}';

    protected $description = '安装 xditn-laravel-mmodule 脚手架';

    private array $defaultExtensions = [
        'bcmath',
        'ctype',
        'intl',
        'dom',
        'fileinfo',
        'json',
        'mbstring',
        'openssl',
        'pcre',
        'pdo',
        'tokenizer',
        'xml',
        'pdo_mysql',
    ];

    protected bool $isFinished = false;

    protected bool $isProd;

    public function handle(): void
    {
        $this->detectionEnvironment();
        $this->isProd = $this->option('prod');

        if (extension_loaded('pcntl')) {
            $this->trap([SIGTERM, SIGQUIT, SIGINT], function () {
                if (! $this->isFinished) {
                    $this->rollback();
                }
                exit;
            });
        }

        try {
            if (! File::exists(app()->environmentFile())) {
                $this->askForCreatingDatabase();
            }

            $this->addPsr4Autoload();
            $this->publishConfig();
            app(Composer::class)->dumpAutoloads();
            $this->isFinished = true;
            $this->info('🎉 xditn-laravel-mmodule 脚手架已安装，请在宿主项目的 modules/ 目录维护业务模块。');
        } catch (Throwable $e) {
            $this->rollback();
            $this->error($e->getMessage());
        }
    }

    protected function rollback(): void
    {
        try {
            MModule::deleteConfigFile();
            MModule::deleteApiConfigFile();

            if (File::exists(app()->environmentFile())) {
                File::delete(app()->environmentFile());
            }

            if (config('database.connections.'.DB::getDefaultConnection())) {
                $databaseConfig = config('database.connections.'.DB::getDefaultConnection());
                $databaseName = $databaseConfig['database'];
                app(ConnectionFactory::class)->make($databaseConfig)->select("drop database $databaseName");
            }
        } catch (Throwable) {
        }
    }

    protected function detectionEnvironment(): void
    {
        $this->checkPHPVersion();
        $this->checkExtensions();
    }

    private function checkExtensions(): void
    {
        $loadedExtensions = Collection::make(get_loaded_extensions())->map(fn ($item) => strtolower($item));
        Collection::make($this->defaultExtensions)
            ->map(fn ($extension) => strtolower($extension))
            ->diff($loadedExtensions)
            ->each(fn ($missingExtension) => $this->error("$missingExtension extension 未安装"));
    }

    private function checkPHPVersion(): void
    {
        version_compare(PHP_VERSION, '8.2.0', '<') && $this->error('PHP 版本应 >= 8.2');
    }

    private function createDatabase(string $databaseName): void
    {
        $databaseConfig = config('database.connections.'.DB::getDefaultConnection());
        $databaseConfig['database'] = null;
        app(ConnectionFactory::class)->make($databaseConfig)->statement(
            "CREATE DATABASE IF NOT EXISTS `$databaseName` DEFAULT CHARSET utf8mb4 COLLATE utf8mb4_general_ci"
        );
    }

    protected function copyEnvFile(): void
    {
        if (! File::exists(app()->environmentFilePath())) {
            File::copy(app()->environmentFilePath().'.example', app()->environmentFilePath());
        } else {
            $this->error('【.env】创建失败，请手动创建！');
        }
    }

    protected function getEnvFileContent(): string
    {
        return File::get(app()->basePath().DIRECTORY_SEPARATOR.'.env.example');
    }

    protected function publishConfig(): void
    {
        try {
            $this->runPublishCommands();
            $this->info('配置文件与模块表迁移已发布，模块代码请在宿主项目 modules/ 目录维护。');
        } catch (Throwable $e) {
            throw new FailedException($e->getMessage());
        }
    }

    /**
     * 历史错误命名的模块表迁移。
     */
    protected array $legacyModuleMigrations = [
        '2022_11_14_034127_module.php',
    ];

    private function runPublishCommands(): void
    {
        $this->info('正在运行发布命令...');

        $commands = [
            'key:generate',
            'vendor:publish --tag=xditn-config --force',
            'vendor:publish --tag=xditn-module --force',
        ];

        foreach ($commands as $command) {
            Process::run(Application::formatCommandString($command))->throw();
        }

        $this->removeLegacyModuleMigrations();

        // 宿主迁移 + 默认模块安装（DefaultInstallCommand 会再次强制同步迁移并先跑宿主 migrate）
        $this->call('xditn:default:install', [
            '--force' => true,
        ]);
    }

    /**
     * 删除旧命名迁移，防止与新文件同时 create 同一张表。
     */
    protected function removeLegacyModuleMigrations(): void
    {
        foreach ($this->legacyModuleMigrations as $filename) {
            $path = database_path('migrations/'.$filename);
            if (File::exists($path)) {
                File::delete($path);
                $this->warn("已移除冲突的旧迁移文件: {$filename}");
            }
        }
    }

    protected function askForCreatingDatabase(): void
    {
        $appName = text('请输入应用名称', 'eg. xditn', required: '应用名称必须填写');
        $appUrl = $this->askForAppUrl();
        $databaseName = text('请输入数据库名称', required: '请输入数据库名称', validate: fn ($value) => preg_match(
            '/[a-zA-Z_]{1,100}/',
            $value
        ) ? null : '数据库名称只支持a-z和A-Z以及下划线_');
        $prefix = text('请输入数据库表前缀', 'eg. xditn_');
        $dbHost = text('请输入数据库主机地址', 'eg. 127.0.0.1', '127.0.0.1', required: '请输入数据库主机地址');
        $dbPort = text('请输入数据库主机端口', 'eg. 3306', '3306', required: '请输入数据库主机端口');
        $dbUsername = text('请输入数据的用户名', 'eg. root', 'root', required: '请输入数据的用户名');
        $dbPassword = text('请输入数据库密码');

        $this->info("正在创建数据库 [$databaseName]...");
        $this->createDatabase($databaseName);
        $this->info("数据库 [$databaseName] 创建成功");
        $this->updateEnvFile($appName, $appUrl, $databaseName, $prefix, $dbHost, $dbPort, $dbUsername, $dbPassword);
        Schema::defaultStringLength(191);
    }

    private function askForAppUrl(): string
    {
        return text(
            label: '请配置应用的 URL',
            placeholder: 'eg. https://127.0.0.1:8080',
            default: $this->isProd ? 'https://' : 'http://127.0.0.1:8080',
            required: '应用的 URL 必须填写',
            validate: fn ($value) => filter_var(
                $value,
                FILTER_VALIDATE_URL
            ) ? null : '应用URL不符合规则'
        );
    }

    private function updateEnvFile(
        string $appName,
        string $appUrl,
        string $databaseName,
        string $prefix,
        string $dbHost,
        string $dbPort,
        string $dbUsername,
        string $dbPassword
    ): void {
        $env = explode("\n", $this->getEnvFileContent());

        foreach ($env as &$value) {
            foreach (
                [
                    'APP_NAME' => $appName,
                    'APP_ENV' => $this->isProd ? 'production' : 'local',
                    'APP_DEBUG' => $this->isProd ? 'false' : 'true',
                    'APP_URL' => $appUrl,
                    'DB_HOST' => $dbHost,
                    'DB_PORT' => $dbPort,
                    'DB_DATABASE' => $databaseName,
                    'DB_USERNAME' => $dbUsername,
                    'DB_PASSWORD' => $dbPassword,
                    'DB_PREFIX' => $prefix,
                    'DB_CONNECTION' => 'mysql',
                    'XDITN_MODULE_AUTOLOAD' => 'true',
                    'XDITN_MODULE_DRIVER' => 'database',
                ] as $key => $newValue
            ) {
                if (Str::contains($value, $key) && ! Str::contains($value, 'VITE_')) {
                    $value = $this->resetEnvValue($value, $newValue);
                }
            }
        }

        $this->copyEnvFile();
        File::put(app()->environmentFile(), implode("\n", $env));
        app()->bootstrapWith([LoadEnvironmentVariables::class, LoadConfiguration::class]);
    }

    protected function resetEnvValue(string $originValue, string $newValue): string
    {
        if (Str::contains($originValue, '=')) {
            $originValue = explode('=', $originValue);
            $originValue[1] = $newValue;

            return implode('=', $originValue);
        }

        return $originValue;
    }

    protected function addPsr4Autoload(): void
    {
        $composerFile = base_path('composer.json');
        $composerJson = json_decode(File::get($composerFile), true);
        $composerJson['autoload']['psr-4']['Modules\\'] = 'modules/';

        File::put(
            $composerFile,
            json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $this->info('composer dump autoload..., 请耐心等待');
        app(Composer::class)->dumpAutoloads();
    }
}
