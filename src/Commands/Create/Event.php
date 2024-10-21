<?php

namespace Xditn\Commands\Create;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Xditn\Commands\XditnCommand;
use Xditn\MModule;

/**
 * 事件生成命令
 */
class Event extends XditnCommand
{
    /**
     * 控制台命令的名称和签名
     *
     * @var string
     */
    protected $signature = 'xditn:make:event {module} {name}';

    /**
     * 控制台命令的描述
     *
     * @var string
     */
    protected $description = '创建模块事件';

    /**
     * 执行命令的处理逻辑
     *
     * @return void
     */
    public function handle(): void
    {
        $eventPath = MModule::getModuleEventPath($this->argument('module'));
        $file = $eventPath.$this->getEventFile();
        if (File::exists($file) && ! $this->confirmOverwrite($file)) {
            return;
        }
        File::put($file, $this->generateStubContent());
        $this->info(File::exists($file) ? "$file 已创建" : "$file 创建失败");
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
     * 获取事件文件名
     *
     * @return string
     */
    protected function getEventFile(): string
    {
        return $this->getEventName().'.php';
    }

    /**
     * 获取事件名称
     *
     * @return string
     */
    protected function getEventName(): string
    {
        return Str::of($this->argument('name'))->whenContains(
                'Event',
                fn ($str) => $str,
                fn ($str) => $str->append('Event')
            )->ucfirst()->toString();
    }

    /**
     * 生成存根内容
     *
     * @return string
     */
    protected function generateStubContent(): string
    {
        return Str::of(
            File::get(dirname(__DIR__).DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'event.stub')
        )->replace(['{namespace}', '{event}'], [
            trim(MModule::getModuleEventsNamespace($this->argument('module')), '\\'),
            $this->getEventName(),
        ])->toString();
    }
}
