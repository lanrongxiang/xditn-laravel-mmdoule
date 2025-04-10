<?php

namespace Xditn\Commands\Create;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Xditn\Commands\XditnCommand;
use Xditn\MModule;

/**
 * 监听器生成命令
 */
class Listener extends XditnCommand
{
    /**
     * 命令名称和签名
     *
     * @var string
     */
    protected $signature = 'xditn:make:listener {module} {name} {--event= : 自动生成关联的事件类}';

    /**
     * 控制台命令的描述
     *
     * @var string
     */
    protected $description = '创建模块事件监听器';

    /**
     * 执行命令的处理逻辑
     *
     * @return void
     */
    public function handle(): void
    {
        $module = $this->argument('module');
        $listenerPath = MModule::getModuleListenersPath($module);
        $file = $listenerPath.$this->getListenerFile();

        if (File::exists($file) && ! $this->confirmOverwrite($file)) {
            return;
        }

        // 生成监听器
        File::put($file, $this->generateStubContent());
        $this->info(File::exists($file) ? "$file 已创建" : "$file 创建失败");

        // 如果指定了自动生成事件
        if ($this->option('event')) {
            $this->generateEvent($module);
        }
    }

    /**
     * 生成关联的事件类
     *
     * @param string $module
     * @return void
     */
    protected function generateEvent(string $module): void
    {
        $eventName = $this->option('event');

        // 如果用户只提供了事件名的一部分，自动补全
        if (!Str::contains($eventName, 'Event')) {
            $eventName .= 'Event';
        }

        $this->call('xditn:make:event', [
            'module' => $module,
            'name' => $eventName
        ]);

        // 更新监听器文件，添加事件类型提示
        $listenerPath = MModule::getModuleListenersPath($module);
        $file = $listenerPath.$this->getListenerFile();

        if (File::exists($file)) {
            $eventNamespace = trim(MModule::getModuleEventsNamespace($module), '\\');
            $eventClass = Str::of($eventName)
                             ->whenContains('Event', fn ($str) => $str, fn ($str) => $str->append('Event'))
                             ->ucfirst()
                             ->toString();

            $content = File::get($file);
            $content = Str::replace(
                'public function handle($event)',
                "public function handle(\\{$eventNamespace}\\{$eventClass} \$event)",
                $content
            );

            File::put($file, $content);
        }
    }

    /**
     * 确认是否覆盖已存在的文件
     *
     * @param  string  $file
     * @return bool
     */
    protected function confirmOverwrite(string $file): bool
    {
        return $this->ask("$file 已存在，是否替换它?", 'Y') === 'Y';
    }

    /**
     * 获取监听器文件名
     *
     * @return string
     */
    protected function getListenerFile(): string
    {
        return $this->getListenerName().'.php';
    }

    /**
     * 获取监听器名称
     *
     * @return string
     */
    protected function getListenerName(): string
    {
        return Str::of($this->argument('name'))
                  ->whenContains('Listener', fn ($str) => $str, fn ($str) => $str->append('Listener'))
                  ->ucfirst()
                  ->toString();
    }

    /**
     * 生成存根内容
     *
     * @return string
     */
    protected function generateStubContent(): string
    {
        return Str::of(
            File::get(dirname(__DIR__).DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'listener.stub')
        )->replace(['{namespace}', '{listener}'], [
            trim(MModule::getModuleListenersNamespace($this->argument('module')), '\\'),
            $this->getListenerName(),
        ])->toString();
    }
}