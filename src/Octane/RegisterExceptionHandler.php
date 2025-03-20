<?php

namespace Xditn\Octane;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Xditn\Exceptions\Handler;

/**
 * 注册异常处理器
 *
 * 在 Octane 中处理来自控制面板的请求
 */
class RegisterExceptionHandler
{
    /**
     * 处理事件
     *
     * @param  mixed  $event
     * @return void
     */
    public function handle(mixed $event): void
    {
        if (isRequestFromDashboard()) {
            $event->sandbox->singleton(ExceptionHandler::class, Handler::class);
        }
    }
}
