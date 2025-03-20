<?php

namespace Xditn\Exceptions;

use Closure;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use SplFileObject;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Xditn\Events\ReportException;

class Handler extends ExceptionHandler
{
    /**
     * 自定义日志级别的异常类型列表
     *
     * @var array<class-string<Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [// 在这里可以指定需要自定义日志级别的异常类型
    ];

    /**
     * 不需要报告的异常类型列表
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [// 这里可以指定不需要报告的异常
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
     * 创建新的异常处理实例
     */
    public function __construct(Container|Closure $container)
    {
        $this->container = $container instanceof Closure ? $container() : $container;
        parent::__construct($this->container);
    }

    /**
     * 注册应用的异常处理回调
     *
     * @return void
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (! ($e instanceof WebhookException) && method_exists($this->container, 'terminating')) {
                $this->container->terminating(fn () => ReportException::dispatch($e));
            }
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
        $e = match (true) {
            $e instanceof ValidationException => new FailedException('验证错误: '.$e->getMessage()),
            $e instanceof ThrottlesExceptions => new FailedException('请求过于频繁，请稍后再试'),
            $e instanceof AuthenticationException => new FailedException('登录失效，请重新登录'),
            $e instanceof NotFoundHttpException => new Exception(
                '路由 ['.$request->getRequestUri().'] 未找到或未注册，请检查路由是否正确'
            ),
            $e instanceof MethodNotAllowedHttpException => new Exception(
                '路由 HTTP 请求方法错误，当前请求方法: '.$request->getMethod().'，请检查对应路由 HTTP 请求方法是否正确'
            ),
            $e instanceof QueryException => new Exception('数据库报错: '.$e->errorInfo[2]),
            $e instanceof ModelNotFoundException => new Exception('模型找不到: '.$e->getMessage()),
            default => $e,
        };

        return parent::render($request, $e)->header('Access-Control-Allow-Origin', '*')->header(
                'Access-Control-Allow-Methods',
                '*'
            )->header('Access-Control-Allow-Headers', '*');
    }

    /**
     * 将异常转换为数组
     *
     * @return array<string>
     */
    protected function convertExceptionToArray(Throwable $e): array
    {
        return config('app.debug') ? [
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $this->getTraceFileContent($e),
        ] : [
            'message' => $e->getMessage() ?: '服务器错误',
        ];
    }

    /**
     * 获取异常的调用堆栈内容
     */
    protected function getTraceFileContent(Throwable $e): array
    {
        $traces = collect($e->getTrace())->map(fn ($trace) => Arr::except($trace, ['args']))->map(function ($trace) {
            if (isset($trace['file'])) {
                $trace['content'] = $this->getFileContents($trace['file'], $trace['line']);
                $trace['path'] = $trace['file'];
                $trace['file'] = Str::of($trace['file'])->replace(base_path().DIRECTORY_SEPARATOR, '');
            }

            return $trace;
        })->all();
        array_unshift($traces, [
            'file' => Str::of($e->getFile())->replace(base_path().DIRECTORY_SEPARATOR, ''),
            'line' => $e->getLine(),
            'path' => $e->getFile(),
            'content' => $this->getFileContents($e->getFile(), $e->getLine()),
        ]);

        return $traces;
    }

    /**
     * 获取指定文件和行的内容
     */
    protected function getFileContents(string $filename, int $line): array
    {
        $contents = [];
        $file = new SplFileObject($filename);
        $start = max($line - 10, 0);
        for ($i = $start; $i <= $line + 5; $i++) {
            $file->seek($i - 1);
            if ($content = $file->current()) {
                $contents[] = [
                    'line' => $i,
                    'content' => $content,
                ];
            }
        }

        return $contents;
    }
}
