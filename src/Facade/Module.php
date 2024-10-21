<?php

declare(strict_types=1);

namespace Xditn\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * 模块门面类
 *
 * 提供模块的静态接口
 *
 * @method static all()
 * @method static create(array $module)
 * @method static update(string $name, array $module)
 * @method static delete(string $name)
 * @method static disOrEnable(string $name)
 */
class Module extends Facade
{
    /**
     * 获取门面访问器
     *
     * @return string
     */
    public static function getFacadeAccessor(): string
    {
        return 'module';
    }
}
