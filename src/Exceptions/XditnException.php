<?php

declare(strict_types=1);

namespace Xditn\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Xditn\Enums\Code;
use Xditn\Enums\Enum;

class XditnException extends HttpException
{
    /**
     * 异常代码，默认为 0
     *
     * @var int
     */
    protected $code = 0;

    /**
     * XditnException 构造函数
     *
     * @param  string  $message 异常消息，默认为空字符串
     * @param  int|Code  $code    异常代码，可以是整数或枚举类型
     */
    public function __construct(string $message = '', int|Code $code = 0)
    {
        // 如果传入的异常代码是 Enum 枚举类型，则获取其实际值
        if ($code instanceof Enum) {
            $code = $code->value();
        }
        // 如果当前类的异常代码是枚举类型并且未传入异常代码，则使用类定义的异常代码
        if ($this->code instanceof Enum && ! $code) {
            $code = $this->code->value();
        }
        // 调用父类的构造函数，设置状态码、消息和异常代码
        parent::__construct($this->statusCode(), $message ?: $this->message, null, [], $code);
    }

    /**
     * 获取状态码
     *
     * @return int 返回 HTTP 状态码，默认 500
     */
    public function statusCode(): int
    {
        return 500;
    }

    /**
     * 渲染异常信息为数组
     *
     * @return array 返回包含异常代码和消息的数组
     */
    public function render(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
        ];
    }
}
