<?php

declare(strict_types=1);

namespace Xditn\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Termwind\ask;
use function Termwind\render;
use Xditn\Facade\Module;

/**
 * 抽象基础命令类，提供模块相关的基础功能
 */
abstract class XditnCommand extends Command
{
    // 命令名称
    protected $name;

    public function __construct()
    {
        parent::__construct();
        // 如果没有定义 signature 但定义了 name，则设置 signature
        if (! property_exists($this, 'signature') && property_exists($this, 'name') && $this->name) {
            $this->signature = $this->name.' {module}';
        }
    }

    // 初始化方法
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // 检查模块是否存在
        if ($input->hasArgument('module') && ! Module::getEnabled()->pluck('name')->merge(
                Collection::make(config('xditn.module.default'))
            )->contains(lcfirst($input->getArgument('module')))) {
            $this->error(sprintf('模块 [%s] 未找到', $input->getArgument('module')));
            exit;
        }
    }

    // 提问方法
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

    // 信息输出方法
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

    // 错误输出方法
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
}
