<?php

namespace Xditn\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Xditn\Enums\Code;

class Handler extends ExceptionHandler
{
    /**
     * 自定义日志级别的异常类型列表
     *
     * @var array<class-string<Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        // 在这里可以指定需要自定义日志级别的异常类型
    ];

    /**
     * 不需要报告的异常类型列表
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        // 这里可以指定不需要报告的异常
    ];

    /**
     * 在验证异常时，永远不会闪存到 session 的输入字段
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * 注册应用的异常处理回调
     *
     * @return void
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // 在这里自定义异常报告逻辑
        });
    }

    /**
     * 渲染异常为 HTTP 响应
     *
     * @param  Request  $request
     * @param  Throwable  $e
     * @return JsonResponse|Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $e): JsonResponse|Response
    {
        // 获取异常消息
        $message = $e->getMessage();

        // 如果异常类有 `getStatusCode` 方法，且状态码为 404，修改消息为“路由未找到或未注册”
        if (method_exists($e, 'getStatusCode') && $e->getStatusCode() == Response::HTTP_NOT_FOUND) {
            $message = '路由未找到或未注册';
        }

        // 将异常包装为 FailedException，如果是 CatchException 使用其代码，否则使用默认失败代码
        $e = new FailedException($message ?: 'Server Error', $e instanceof XditnException ? $e->getCode() : Code::FAILED);

        // 调用父类的渲染方法生成响应
        $response = parent::render($request, $e);

        // 设置 CORS 头，允许所有来源、方法和头部信息
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', '*');
        $response->header('Access-Control-Allow-Headers', '*');

        // 返回渲染后的响应
        return $response;
    }
}
