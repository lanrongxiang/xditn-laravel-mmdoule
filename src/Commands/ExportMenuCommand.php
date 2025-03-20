<?php

namespace Xditn\Commands;

use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Xditn\Base\XditnModel;
use Xditn\MModule;

/**
 * 导出菜单命令类
 *
 * 此命令用于将模块的菜单数据导出为 Seeder 文件。
 */
class ExportMenuCommand extends XditnCommand
{
    /**
     * 命令签名和选项
     *
     * @var string
     */
    protected $signature = 'xditn:export:menu {--p : 是否使用树形结构}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '导出指定模块的菜单数据';

    /**
     * 初始化命令
     *
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // 初始化逻辑可以在这里添加
    }

    /**
     * 命令执行入口
     *
     * @return void
     */
    public function handle(): void
    {
        // 获取所有模块信息
        $modules = MModule::getAllModules();

        try {
            // 让用户选择要导出的模块
            $selectedModulesTitle = $this->choice(
                '选择导出菜单的模块',
                $modules->pluck('title')->toArray(),
                attempts: 1,
                multiple: true
            );
        } catch (\Exception $e) {
            $this->error('未选择任何模块');
            exit;
        }

        // 过滤出选择的模块名称
        $module = [];
        $modules->each(function ($item) use ($selectedModulesTitle, &$module) {
            if (in_array($item['title'], $selectedModulesTitle)) {
                $module[] = $item['name'];
            }
        });

        // 创建模型实例并获取菜单数据
        $model = $this->createModel();
        $data = $model->whereIn('module', $module)->get()->toTree();

        // 将数据转换为 PHP 数组格式
        $data = 'return '.var_export(json_decode($data, true), true).';';

        // 导出为 Seeder 文件
        $this->exportSeed($data, $module);

        $this->info('模块菜单导出成功');
    }

    /**
     * 导出 Seeder 文件
     *
     * @param  string  $data   菜单数据
     * @param  array  $module 模块名称
     * @return void
     */
    protected function exportSeed(string $data, array $module): void
    {
        $module = $module[0]; // 获取第一个模块名称

        // 获取 Seeder 模板文件内容
        $stub = File::get(__DIR__.DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'menuSeeder.stub');

        // 生成 Seeder 类名
        $class = ucfirst($module).'MenusSeeder';

        // 替换模板中的占位符为实际内容
        $stub = str_replace('{CLASS}', $class, $stub);
        $stub = str_replace('{menus}', $data, $stub);

        // 写入 Seeder 文件
        File::put(MModule::getModuleSeederPath($module).$class.'.php', $stub);
    }

    /**
     * 创建模型实例
     *
     * @return XditnModel
     */
    protected function createModel(): XditnModel
    {
        // 使用匿名类创建模型实例
        return new class() extends XditnModel
        {
            protected $table = 'permissions'; // 指定数据库表名
        };
    }
}
