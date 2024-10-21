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
use Illuminate\Support\Str;
use Throwable;
use Xditn\Support\Installer;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;
use Xditn\Exceptions\FailedException;
use Xditn\Facade\Module;
use Xditn\Support\Composer;
use Xditn\MModule;

class InstallCommand extends XditnCommand
{
    protected $signature = 'xditn:install {--reinstall}';

    protected $description = '安装 xditn-laravel-mmdoule';

    private array  $defaultExtensions = [
        'BCMath',
        'Ctype',
        'DOM',
        'Fileinfo',
        'JSON',
        'Mbstring',
        'OpenSSL',
        'PCRE',
        'PDO',
        'Tokenizer',
        'XML',
        'pdo_mysql',
    ];
    private array  $defaultModules    = ['develop', 'user', 'common'];
    private string $optionalModule    = 'Permissions';

    /**
     * 主处理函数
     *
     * @return void
     *
     * @throws Throwable
     */
    public function handle(): void
    {
        $this->reinstall();
        try {
            if (!File::exists(app()->environmentFile())) {
                $this->detectionEnvironment();
                $this->askForCreatingDatabase();
            }
            $installPermissions = confirm('是否安装权限模块目录?', true);
            $installSystem      = confirm('是否安装系统管理模块目录?', true);
            if ($installPermissions) {
                $this->defaultModules[] = $this->optionalModule;
            }
            if ($installSystem) {
                $this->defaultModules[] = 'System';
            }
            // 安装模块
            $this->installModules();
            $this->addPsr4Autoload();
            $this->publishConfig($installPermissions, $installSystem);
            $this->addPsr4Autoload();
            $this->info('🎉 xditn-laravel-mmdoule 已安装, 欢迎!');
        } catch (Throwable $e) {
            File::delete(app()->environmentFilePath());
            $this->reinstall(true);
            MModule::deleteConfigFile();
            $this->error($e->getMessage());
        }
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
        Installer::copyModules($this->defaultModules);
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
        $loadedExtensions = Collection::make(get_loaded_extensions())->map(fn($item) => strtolower($item)); // 使用箭头函数简化
        Collection::make($this->defaultExtensions)->map(fn($extension) => strtolower($extension)) // 转换扩展名为小写
                  ->diff($loadedExtensions)                                                       // 查找未安装的扩展
                  ->each(fn($missingExtension) => $this->error("$missingExtension extension 未安装")); // 报错缺少的扩展
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
     * @param string $databaseName
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    private function createDatabase(string $databaseName): void
    {
        $databaseConfig             = config('database.connections.' . DB::getDefaultConnection());
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
        if (!File::exists(app()->environmentFilePath())) {
            File::copy(app()->environmentFilePath() . '.example', app()->environmentFilePath());
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
        return File::get(app()->basePath() . DIRECTORY_SEPARATOR . '.env.example');
    }

    /**
     * 发布配置
     *
     * @param bool $installPermissions
     * @param bool $installSystem
     *
     * @return void
     */
    protected function publishConfig(bool $installPermissions = false, bool $installSystem = false): void
    {
        try {
            $this->runPublishCommands($installPermissions, $installSystem);
            $this->info('模块安装成功，模块信息存储在 [storage/app/module.json] 文件');
        } catch (\Exception|Throwable $e) {
            throw new FailedException($e->getMessage());
        }
    }

    /**
     * 运行发布命令
     *
     * @param bool $installPermissions
     * @param bool $installSystem
     *
     * @return void
     */
    private function runPublishCommands(bool $installPermissions = false, bool $installSystem = false): void
    {
        $this->info("正在运行 发布命令...");
        $commands = [
            'key:generate',
            'vendor:publish --tag=xditn-config --force',
            'vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"',
            'migrate',
        ];
        foreach ($commands as $command) {
            Process::run(Application::formatCommandString($command))->throw();
        }
        //安装默认模板
        $installer = MModule::getModuleInstaller('develop');
        $installer->install();
        $installer = MModule::getModuleInstaller('user');
        $installer->install();
        if ($installPermissions) {
            $installer = MModule::getModuleInstaller('permissions');
            $installer->install();
        }
        if ($installSystem) {
            $installer = MModule::getModuleInstaller('system');
            $installer->install();
        }
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
        $appUrl       = $this->askForAppUrl();
        $databaseName = text('请输入数据库名称', required: '请输入数据库名称', validate: fn($value) => preg_match(
            '/[a-zA-Z_]{1,100}/',
            $value
        ) ? null : '数据库名称只支持a-z和A-Z以及下划线_');
        $prefix       = text('请输入数据库表前缀', 'eg. xditn_');
        $dbHost       = text('请输入数据库主机地址', 'eg. 127.0.0.1', '127.0.0.1', required: '请输入数据库主机地址');
        $dbPort       = text('请输入数据库主机端口', 'eg. 3306', '3306', required: '请输入数据库主机端口');
        $dbUsername   = text('请输入数据的用户名', 'eg. root', 'root', required: '请输入数据的用户名');
        $dbPassword   = text('请输入数据库密码');
        // 更新 .env 文件
        $this->updateEnvFile($appUrl, $databaseName, $prefix, $dbHost, $dbPort, $dbUsername, $dbPassword);
        $this->info("正在创建数据库 [$databaseName]...");
        $this->createDatabase($databaseName);
        $this->info("数据库 [$databaseName] 创建成功");
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
            default: 'https://127.0.0.1:8080',
            required: '应用的 URL 必须填写',
            validate: fn($value) => filter_var(
                $value,
                FILTER_VALIDATE_URL
            ) ? null : '应用URL不符合规则'
        );
    }

    /**
     * 更新 .env 文件
     *
     * @param string $appUrl
     * @param string $databaseName
     * @param string $prefix
     * @param string $dbHost
     * @param string $dbPort
     * @param string $dbUsername
     * @param string $dbPassword
     *
     * @return void
     */
    private function updateEnvFile(
        string $appUrl,
        string $databaseName,
        string $prefix,
        string $dbHost,
        string $dbPort,
        string $dbUsername,
        string $dbPassword
    ): void{
        $env = explode("\n", $this->getEnvFileContent());
        foreach ($env as &$value) {
            foreach (
                [
                    'APP_URL'              => $appUrl,
                    'DB_HOST'              => $dbHost,
                    'DB_PORT'              => $dbPort,
                    'DB_DATABASE'          => $databaseName,
                    'DB_USERNAME'          => $dbUsername,
                    'DB_PASSWORD'          => $dbPassword,
                    'DB_PREFIX'            => $prefix,
                    'DB_CONNECTION'        => 'mysql',
                    'ALIOSS_BUCKET'        => '',
                    'ALIOSS_ACCESS_ID'     => '',
                    'ALIOSS_ACCESS_SECRET' => '',
                    'ALIOSS_ENDPOINT'      => '',
                    'ALIOSS_UPLOAD_DIR'    => '',
                ] as $key => $newValue
            ) {
                if (Str::contains($value, $key) && !Str::contains($value, 'VITE_')) {
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

    /**
     * 添加 PSR-4 自动加载
     *
     * @return void
     */
    protected function addPsr4Autoload(): void
    {
        $composerFile                                                         = base_path('composer.json');
        $composerJson                                                         = json_decode(
            File::get($composerFile),
            true
        );
        $composerJson['autoload']['psr-4']['Modules\\']                       = 'modules/';
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
     * 重新安装
     *
     * @param bool $sign
     *
     * @return void
     *
     */
    protected function reinstall(bool $sign = false): void
    {
        if ($this->option('reinstall') || $sign) {
            $this->deleteInstalledModules();
            DB::statement("DROP DATABASE IF EXISTS `" . config('database.connections.mysql.database') . "`");
            File::delete(app()->environmentFile());
        }
    }

    /**
     * 删除已安装的模块
     *
     * @return void
     */
    private function deleteInstalledModules(): void
    {
        Module::all()->each(fn($module) => Module::delete($module['name']));
        collect($this->defaultModules)->each(fn($module) => MModule::deleteModulePath($module));
    }
}
