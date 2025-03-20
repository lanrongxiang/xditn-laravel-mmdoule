<?php

namespace Xditn\Base\modules\Common\Enums;

use Xditn\Enums\Enum;

enum UploadDriver: string implements Enum
{
    case OSS = 'oss';
    case COS = 'cos';
    case QINIU = 'qiniu';

    public function value(): string
    {
        // TODO: Implement value() method.
        return match ($this) {
            self::OSS => 'oss',
            self::COS => 'cos',
            self::QINIU => 'qiniu',
        };
    }

    public function name(): string
    {
        // TODO: Implement name() method.
        return match ($this) {
            self::OSS => '阿里云OSS',
            self::COS => '腾讯COS',
            self::QINIU => '七牛云',
        };
    }

    public function assert(string $value): bool
    {
        return $this->value === $value;
    }
}
