<?php

declare(strict_types=1);

namespace Xditn\Support\Macros;

/**
 * 宏扩展注册类
 */
class MacrosRegister
{
    public function __construct(
        protected BlueprintMacros $blueprint,
        protected Collection $collection,
        protected Builder $builder,
        protected Router $router
    ) {
    }

    /**
     * 启动所有宏扩展
     */
    public function boot(): void
    {
        $this->blueprint->boot();
        $this->collection->boot();
        $this->builder->boot();
        $this->router->boot();
    }
}
