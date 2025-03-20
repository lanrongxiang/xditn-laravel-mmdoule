<?php

namespace Xditn\Commands;

use Illuminate\Support\Str;
use Xditn\MModule;
use Xditn\Support\Module\Installer;

class ModuleInstallCommand extends XditnCommand
{
    protected $signature = 'xditn:module:install{--f}';

    protected $description = '安装模块';

    public function handle(): void
    {
        $installers = $this->gatherModuleInstallers();

        // 获取模块信息
        $modules = array_map(fn ($installer) => $installer->getInfo(), $installers);

        try {
            // 让用户选择要安装的模块
            $selectedModulesTitle = $this->choice(
                '选择你要安装的模块',
                array_column($modules, 'title'),
                attempts: 1,
                multiple: true
            );
        } catch (\Throwable $e) {
            $this->error('未选择任何模块');
            exit;
        }

        // 筛选出用户选择的模块的安装器
        $selectedInstallers = $this->getSelectedInstallers($modules, $selectedModulesTitle);

        // 检查是否强制安装
        if ($this->option('f')) {
            $this->handleForcedInstall($selectedInstallers, $selectedModulesTitle);
        } else {
            $this->installModules($selectedInstallers, $selectedModulesTitle);
        }
    }

    /**
     * 收集所有模块的安装器
     *
     * @return Installer[]
     */
    protected function gatherModuleInstallers(): array
    {
        $installers = [];

        foreach (MModule::getModulesPath() as $modulePath) {
            if (is_dir($modulePath)) {
                try {
                    $moduleName = Str::of($modulePath)->explode(DIRECTORY_SEPARATOR)->last();
                    $installers[] = MModule::getModuleInstaller($moduleName);
                } catch (\Throwable $e) {
                    // 捕获异常但不做处理，以便继续执行其他模块的安装检查
                }
            }
        }

        return $installers;
    }

    /**
     * 获取用户选择的模块的安装器
     *
     * @param  array  $modules 所有模块信息
     * @param  array  $selectedModulesTitle 用户选择的模块标题
     * @return array 选择的模块安装器
     */
    protected function getSelectedInstallers(array $modules, array $selectedModulesTitle): array
    {
        $selectedInstallers = [];

        foreach ($selectedModulesTitle as $title) {
            foreach ($modules as $module) {
                if ($module['title'] === $title) {
                    $selectedInstallers[] = MModule::getModuleInstaller($module['name']);
                    break;
                }
            }
        }

        return $selectedInstallers;
    }

    /**
     * 处理强制安装逻辑
     *
     * @param  array  $installers 选择的模块安装器
     * @param  array  $titles 用户选择的模块标题
     * @return void
     */
    protected function handleForcedInstall(array $installers, array $titles): void
    {
        $answer = $this->askFor('强制安装模块，不会删除当前模块的数据库数据。是否继续?', 'y');

        if (in_array(strtolower($answer), ['y', 'yes'])) {
            foreach ($installers as $installer) {
                $installer->uninstall();
                $installer->install();
            }
            $this->info(implode(',', $titles).' 已强制安装');
        }
    }

    /**
     * 安装模块
     *
     * @param  array  $installers 选择的模块安装器
     * @param  array  $titles 用户选择的模块标题
     * @return void
     */
    protected function installModules(array $installers, array $titles): void
    {
        foreach ($installers as $installer) {
            $installer->install();
        }
        $this->info(implode(',', $titles).' 已安装成功');
    }
}
