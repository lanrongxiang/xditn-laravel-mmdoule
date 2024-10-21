<?php

// 命名空间和类的声明

namespace Xditn\Commands\Create;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Xditn\Commands\XditnCommand;
use Xditn\MModule;

/**
 * 创建模型命令类
 *
 * 该类用于通过命令行创建模型文件，基于给定的模块和模型名。
 */
class Model extends XditnCommand
{
    // 命令签名
    protected $signature = 'xditn:make:model {module} {model} {--t= : the model of table name}';

    // 命令描述
    protected $description = '创建 xditn 模块中的模型';

    /**
     * 处理命令
     */
    public function handle(): void
    {
        // 检查表是否存在
        $tableName = $this->getTableName();
        if (! Schema::hasTable($tableName)) {
            $this->error("表结构 [{$tableName}] 未找到");
            exit;
        }

        // 获取模型路径和文件名
        $modelPath = MModule::getModuleModelPath($this->argument('module'));
        $file = $modelPath.$this->getModelFile();

        // 如果文件已存在，询问是否替换
        if (File::exists($file)) {
            $answer = $this->ask("{$file} 已存在，是否要替换它？", 'Y');
            if (! Str::of($answer)->lower()->exactly('y')) {
                exit;
            }
        }

        // 写入模型内容
        File::put($file, $this->getModelContent());

        // 检查文件是否成功创建
        if (File::exists($file)) {
            $this->info("{$file} 已成功创建");
        } else {
            $this->error("{$file} 创建失败");
        }
    }

    /**
     * 获取模型文件名
     *
     * @return string 模型文件名
     */
    protected function getModelFile(): string
    {
        return $this->getModelName().'.php';
    }

    /**
     * 获取模型类名
     *
     * @return string 模型类名
     */
    protected function getModelName(): string
    {
        return Str::of($this->argument('model'))->ucfirst()->toString();
    }

    /**
     * 获取模板内容
     *
     * @return string 模板内容
     */
    protected function getStubContent(): string
    {
        return File::get(dirname(__DIR__).DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'model.stub');
    }

    /**
     * 获取模型内容
     *
     * @return string 模型内容
     */
    protected function getModelContent(): string
    {
        return Str::of($this->getStubContent())
                  ->replace(
                      ['{namespace}', '{model}', '{table}', '{fillable}'],
                      [$this->getModelNamespace(), $this->getModelName(), $this->getTableName(), $this->getFillable()]
                  )->toString();
    }

    /**
     * 获取模型命名空间
     *
     * @return string 模型命名空间
     */
    protected function getModelNamespace(): string
    {
        return trim(MModule::getModuleModelNamespace($this->argument('module')), '\\');
    }

    /**
     * 获取表名
     *
     * @return string 表名
     */
    protected function getTableName(): string
    {
        return $this->option('t') ?? Str::of($this->argument('model'))->snake()->lcfirst()->toString();
    }

    /**
     * 获取可填充字段
     *
     * @return string 可填充字段
     */
    protected function getFillable(): string
    {
        // 假设 getTableColumns 是一个全局函数或者已经在某处定义
        return collect(getTableColumns($this->getTableName()))->map(fn ($column) => "'{$column}'")->implode(', ');
    }
}
