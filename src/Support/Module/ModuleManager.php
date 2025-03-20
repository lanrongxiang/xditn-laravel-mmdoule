<?php

declare(strict_types=1);

namespace Xditn\Support\Module;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Manager;
use Xditn\Support\Module\Driver\DatabaseDriver;
use Xditn\Support\Module\Driver\FileDriver;

class ModuleManager extends Manager
{
    /**
     * 构造函数
     *
     * @param  Container|Closure  $container 容器实例或闭包
     */
    public function __construct(Container|Closure $container)
    {
        // 如果传入的是闭包，则调用闭包返回容器实例
        if ($container instanceof Closure) {
            $container = $container();
        }

        // 调用父类的构造函数
        parent::__construct($container);
    }

    /**
     * 获取默认驱动
     *
     * @return string|null
     */
    public function getDefaultDriver(): string|null
    {
        // 从配置文件中获取默认驱动，如果没有则返回默认的驱动
        return $this->config->get('xditn.module.driver.default', $this->defaultDriver());
    }

    /**
     * 创建文件驱动
     *
     * @return FileDriver
     */
    public function createFileDriver(): FileDriver
    {
        // 实例化并返回文件驱动
        return new FileDriver();
    }

    /**
     * 创建数据库驱动
     *
     * @return DatabaseDriver
     */
    public function createDatabaseDriver(): DatabaseDriver
    {
        // 实例化并返回数据库驱动
        return new DatabaseDriver();
    }

    /**
     * 默认驱动类型
     *
     * @return string
     */
    protected function defaultDriver(): string
    {
        // 默认使用文件驱动
        return 'file';
    }
}
