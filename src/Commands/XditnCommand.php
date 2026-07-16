<?php

declare(strict_types=1);

namespace Xditn\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Termwind\ask;
use function Termwind\render;
use Xditn\MModule;

/**
 * 抽象基础命令类，提供模块相关的基础功能
 */
abstract class XditnCommand extends Command
{
    protected $name;

    public function __construct()
    {
        parent::__construct();
        if (! property_exists($this, 'signature') && property_exists($this, 'name') && $this->name) {
            $this->signature = $this->name.' {module}';
        }
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        if (! $input->hasArgument('module')) {
            return;
        }

        $module = (string) $input->getArgument('module');
        if ($module === '') {
            return;
        }

        // 只校验模块目录是否存在，避免依赖 admin_modules（安装阶段表可能为空）
        // 切勿使用 exit：会导致父命令（如 xditn:default:install）无提示直接退出
        if (! MModule::isModulePathExist($module)) {
            $this->error(sprintf('模块 [%s] 目录未找到: %s', $module, MModule::getModulePath($module, false)));
            throw new \RuntimeException(sprintf('模块 [%s] 未找到', $module));
        }
    }

    /**
     * 模块名是否在默认模块配置中（不查询数据库）。
     */
    protected function isConfiguredDefaultModule(string $module): bool
    {
        return Collection::make(config('xditn.module.default', []))
            ->map(fn ($name) => Str::lower((string) $name))
            ->contains(Str::lower($module));
    }

    public function askFor(string $question, $default = null, bool $isChoice = false): string|null|int
    {
        $_default = $default ? "<em class='pl-1 text-rose-600'>[$default]</em>" : '';
        $choice = $isChoice ? '<em class="bg-indigo-600 w-5 pl-1 ml-1 mr-1">是</em>或<em class="bg-rose-600 w-4 pl-1 ml-1">否</em>' : '';
        $answer = ask(
            <<<HTML
            <div>
                <div class="px-1 bg-indigo-700">Xditn</div>
                <em class="ml-1">
                    <em class="text-green-700">$question</em>
                    $_default
                    $choice
                    <em class="ml-1">:</em><em class="ml-1"></em>
                </em>
            </div>
HTML
        );
        $this->newLine();

        return $default && ! $answer ? $default : $answer;
    }

    public function info($string, $verbosity = null): void
    {
        render(
            <<<HTML
            <div>
                <div class="px-1 bg-indigo-700">Xditn</div>
                <em class="ml-1">
                    <em class="text-green-700">$string</em>
                </em>
            </div>
HTML
        );
    }

    public function error($string, $verbosity = null): void
    {
        render(
            <<<HTML
            <div>
                <div class="px-1 bg-indigo-700">Xditn</div>
                <em class="ml-1">
                    <em class="text-rose-700">$string</em>
                </em>
            </div>
HTML
        );
    }

    public function warn($string, $verbosity = null): void
    {
        render(
            <<<HTML
            <div>
                <div class="px-1 bg-indigo-700">Xditn</div>
                <em class="ml-1">
                    <em class="text-amber-500">$string</em>
                </em>
            </div>
HTML
        );
    }
}
