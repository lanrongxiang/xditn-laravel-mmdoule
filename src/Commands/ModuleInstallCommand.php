<?php

namespace Xditn\Commands;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Xditn\Facade\Module;
use Xditn\MModule;

class ModuleInstallCommand extends XditnCommand
{
    protected $signature = 'xditn:module:install {module} {--f}';

    protected $description = '安装模块';

    /**
     * 初始化命令
     *
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        if (! $this->option('f')) {
            $moduleName = lcfirst($input->getArgument('module'));
            if ($input->hasArgument('module') && $this->isModuleInstalled($moduleName)) {
                $this->error(sprintf('模块 [%s] 已安装', $moduleName));
                exit;
            }
        }
    }

    /**
     * 处理命令
     *
     * @return void
     */
    public function handle(): void
    {
        $installer = MModule::getModuleInstaller($this->argument('module'));
        $installer->install();
    }

    /**
     * 检查模块是否已安装
     *
     * @param  string  $moduleName
     * @return bool
     */
    private function isModuleInstalled(string $moduleName): bool
    {
        return Module::getEnabled()->pluck('name')->merge(Collection::make(config('xditn.module.default')))->contains(
                $moduleName
            );
    }
}
