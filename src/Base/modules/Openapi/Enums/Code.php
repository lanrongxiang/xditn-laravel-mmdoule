<?php

namespace Xditn\Base\modules\Openapi\Enums;

enum Code: int implements Enum
{
    case SUCCESS = 10000; // 成功的code
    case FAILED = 10001; // 失败的 code
    case APP_KEY_LOST = 10002; // app key 失效
    case SIGNATURE_LOST = 10003; // 签名失效
    case INVALID_APP_KEY = 10004; // 无效app key
    case INVALID_SIGNATURE = 10005; // 无效签名
    case INVALID_TIMESTAMP = 10006; // 无效时间
    case Balance_NOT_ENOUGH = 10007; // 余额不足
    case RATE_LIMIT = 10008; // 限流

    public function message(): string
    {
        return match ($this) {
            self::SUCCESS => 'success',
            self::FAILED => 'failed',
            self::APP_KEY_LOST => 'app key 丢失',
            self::SIGNATURE_LOST => 'signature 丢失',
            self::INVALID_APP_KEY => '无效 app key',
            self::INVALID_SIGNATURE => '无效签名',
            self::INVALID_TIMESTAMP => '无效 timestamp',
            self::Balance_NOT_ENOUGH => '余额不足',
            self::RATE_LIMIT => '请求过于频繁'
        };
    }

    // 自定义 code
    /**
     * @param  mixed  $value
     * @return bool
     */
    public function equal(mixed $value): bool
    {
        return $this->value == $value;
    }
}
