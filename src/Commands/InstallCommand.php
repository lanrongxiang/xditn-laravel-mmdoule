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
use Xditn\Facade\Module;
use Xditn\MModule;
use Xditn\Support\Composer;
use Xditn\Support\Installer;

class InstallCommand extends XditnCommand
{
    protected $signature = 'xditn:install {--prod}';

    protected $description = '安装 xditn-laravel-mmdoule';

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

    private array $defaultModules = ['develop', 'user', 'common'];

    private array $selectedModules = [];

    protected bool $isFinished = false;

    protected bool $isProd;

    /**
     * 主处理函数
     *
     * @return void
     *
     * @throws Throwable
     */
    public function handle(): void
    {
        $this->detectionEnvironment();
        // 是否是生产环境
        $this->isProd = $this->option('prod');
        // 捕捉退出信号
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
            //选择模块
            $this->askModules();
            // 安装模块
            $this->installModules();
            $this->addPsr4Autoload();
            $this->publishConfig();
            app(Composer::class)->dumpAutoloads();
            $this->info('🎉 xditn-laravel-mmdoule 已安装, 欢迎!');
        } catch (Throwable $e) {
            $this->rollback();
            $this->error($e->getMessage());
        }
    }

    protected function askModules(): void
    {
        // 获取所有模块目录
        $allModules = getSubdirectories(__DIR__.'/../Base/modules/');
        // 过滤掉默认模块，保留需要用户选择的模块
        $availableModules = array_diff(
            $allModules,
            array_map('ucfirst', $this->defaultModules)
        );
        // 将 "all" 选项添加到模块列表的最后
        $availableModules = array_values($availableModules); // 确保索引从 0 开始
        $availableModules[] = 'all';
        // 打印模块列表供用户选择
        $this->info('可供选择的模块列表：');
        foreach ($availableModules as $index => $module) {
            $this->line("[$index] $module");
        }
        // 提示用户选择多个模块的下标，使用逗号分隔
        $input = $this->ask('请输入要安装的模块下标（多个模块用逗号分隔）：');
        // 将用户输入的下标解析为数组，并过滤非法输入
        $selectedIndexes = array_filter(
            array_map('trim', explode(',', $input)),
            fn ($index) => is_numeric($index) && isset($availableModules[$index])
        );
        // 判断是否选择了 "all" 选项
        if (in_array(count($availableModules) - 1, $selectedIndexes)) {
            // 如果选择了 "all"，将所有模块加入选择结果中（不包括 "all" 本身）
            $this->selectedModules = array_slice($availableModules, 0, -1);
        } else {
            // 根据用户选择的下标获取模块名
            $this->selectedModules = array_map(
                fn ($index) => $availableModules[$index],
                $selectedIndexes
            );
        }
        // 打印已选择的模块
        if (count($this->selectedModules) > 0) {
            $this->info('已选择的模块：'.implode(', ', $this->selectedModules));
        } else {
            $this->warn('未选择任何有效的模块。');
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
            $this->deleteInstalledModules();
            $databaseConfig = config('database.connections.'.DB::getDefaultConnection());
            $databaseName = $databaseConfig['database'];
            app(ConnectionFactory::class)->make($databaseConfig)->select("drop database $databaseName");
        } catch (\Throwable $e) {
        }
    }

    /**
     * 获取用户选择的模块的安装器
     *
     * @param  array  $modules              所有模块信息
     * @param  array  $selectedModulesTitle 用户选择的模块标题
     * @return array 选择的模块安装器
     */
    protected function getSelectedInstallers(array $modules, array $selectedModulesTitle): array
    {
        $selectedInstallers = [];
        foreach ($selectedModulesTitle as $title) {
            foreach ($modules as $module) {
                if ($module === $title) {
                    $selectedInstallers[] = MModule::getModuleInstaller($module['name']);
                    break;
                }
            }
        }

        return $selectedInstallers;
    }

    /**
     * 安装模块
     *
     *
     * @return void
     */
    protected function installModules(): void
    {
        // 复制模块
        Installer::copyModules([...$this->defaultModules, ...$this->selectedModules]);
    }

    /**
     * 检测环境
     *
     * @return void
     */
    protected function detectionEnvironment(): void
    {
        $this->checkPHPVersion();
        $this->checkExtensions();
    }

    /**
     * 检查 PHP 扩展
     *
     * @return void
     */
    private function checkExtensions(): void
    {
        $loadedExtensions = Collection::make(get_loaded_extensions())->map(fn ($item) => strtolower($item)); // 使用箭头函数简化
        Collection::make($this->defaultExtensions)->map(fn ($extension) => strtolower($extension)) // 转换扩展名为小写
                  ->diff($loadedExtensions)                                                       // 查找未安装的扩展
                  ->each(fn ($missingExtension) => $this->error("$missingExtension extension 未安装")); // 报错缺少的扩展
    }

    /**
     * 检查 PHP 版本
     *
     * @return void
     */
    private function checkPHPVersion(): void
    {
        version_compare(PHP_VERSION, '8.2.0', '<') && $this->error('PHP 版本应 >= 8.2');
    }

    /**
     * 创建数据库
     *
     * @param  string  $databaseName
     * @return void
     *
     * @throws BindingResolutionException
     */
    private function createDatabase(string $databaseName): void
    {
        $databaseConfig = config('database.connections.'.DB::getDefaultConnection());
        $databaseConfig['database'] = null;
        app(ConnectionFactory::class)->make($databaseConfig)->statement(
            "CREATE DATABASE IF NOT EXISTS `$databaseName` DEFAULT CHARSET utf8mb4 COLLATE utf8mb4_general_ci"
        );
    }

    /**
     * 检查并复制 .env 文件
     *
     * @return void
     */
    protected function copyEnvFile(): void
    {
        if (! File::exists(app()->environmentFilePath())) {
            File::copy(app()->environmentFilePath().'.example', app()->environmentFilePath());
        } else {
            $this->error('【.env】创建失败，请手动创建！');
        }
    }

    /**
     * 获取 .env 文件内容
     *
     * @return string
     */
    protected function getEnvFileContent(): string
    {
        return File::get(app()->basePath().DIRECTORY_SEPARATOR.'.env.example');
    }

    /**
     * 发布配置
     *
     * @return void
     */
    protected function publishConfig(): void
    {
        try {
            $this->runPublishCommands();
            $this->info('模块安装成功，模块信息存储在 [storage/app/module.json] 文件');
        } catch (\Exception|Throwable $e) {
            throw new FailedException($e->getMessage());
        }
    }

    /**
     * 运行发布命令
     *
     * @return void
     */
    private function runPublishCommands(): void
    {
        try {
            $this->info('正在运行 发布命令...');
            $commands = [
                'key:generate',
                'vendor:publish --tag=xditn-config',
                'vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"',
            ];
            foreach ($commands as $command) {
                Process::run(Application::formatCommandString($command))->throw();
            }
            //安装默认模板
            foreach (['user', 'develop'] as $name) {
                if ($name == 'user') {
                    MModule::getModuleInstaller($name)->uninstall();
                    MModule::getModuleInstaller($name)->install();
                } else {
                    $this->migrateModule($name);
                }
            }
            //初始化迁移位置不要更改
            Process::run(Application::formatCommandString('migrate'))->throw();
            $this->info('正在运行 安装模块命令...');
            foreach (sortArrayByPriorities(['permissions', 'system'], $this->selectedModules) as $name) {
                MModule::getModuleInstaller($name)->uninstall();
                MModule::getModuleInstaller($name)->install();
            }
        } catch (\Exception|\Throwable $e) {
            $this->warn($e->getMessage());
            $this->rollback();
        }
    }

    protected function migrateModule(string $name): void
    {
        $migrationStr = sprintf('xditn:migrate %s', $name);
        $seedStr = sprintf('xditn:db:seed %s', $name);
        command([$migrationStr, $seedStr]);
    }

    /**
     * 创建数据库
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
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
        // 更新 .env 文件
        $this->info("正在创建数据库 [$databaseName]...");
        $this->createDatabase($databaseName);
        $this->info("数据库 [$databaseName] 创建成功");
        $this->updateEnvFile($appName, $appUrl, $databaseName, $prefix, $dbHost, $dbPort, $dbUsername, $dbPassword);
        // 设置默认字符串长度
        Schema::defaultStringLength(191);
    }

    /**
     * 获取应用 URL
     *
     * @return string
     */
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

    /**
     * 更新 .env 文件
     *
     * @param  string  $appName
     * @param  string  $appUrl
     * @param  string  $databaseName
     * @param  string  $prefix
     * @param  string  $dbHost
     * @param  string  $dbPort
     * @param  string  $dbUsername
     * @param  string  $dbPassword
     * @return void
     */
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
                    'ALIOSS_BUCKET' => '',
                    'ALIOSS_ACCESS_ID' => '',
                    'ALIOSS_ACCESS_SECRET' => '',
                    'ALIOSS_ENDPOINT' => '',
                    'ALIOSS_UPLOAD_DIR' => '',
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

    /**
     * 重置环境变量值
     *
     * @param  string  $originValue
     * @param  string  $newValue
     * @return string
     */
    protected function resetEnvValue(string $originValue, string $newValue): string
    {
        if (Str::contains($originValue, '=')) {
            $originValue = explode('=', $originValue);
            $originValue[1] = $newValue;

            return implode('=', $originValue);
        }

        return $originValue;
    }

    /**
     * 添加 PSR-4 自动加载
     *
     * @return void
     */
    protected function addPsr4Autoload(): void
    {
        $composerFile = base_path('composer.json');
        $composerJson = json_decode(
            File::get($composerFile),
            true
        );
        $composerJson['autoload']['psr-4']['Modules\\'] = 'modules/';
        $composerJson['autoload']['psr-4'][MModule::getModuleRootNamespace()] = str_replace(
            '\\',
            '/',
            MModule::moduleRoot()
        );
        File::put(
            $composerFile,
            json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
        $this->info('composer dump autoload..., 请耐心等待');
        app(Composer::class)->dumpAutoloads();
    }

    /**
     * 删除已安装的模块
     *
     * @return void
     */
    private function deleteInstalledModules(): void
    {
        Module::all()->each(fn ($module) => Module::delete($module['name']));
        collect($this->defaultModules)->each(fn ($module) => MModule::deleteModulePath($module));
    }
}
