<?php

namespace Xditn\Base\modules\Domain\Support;

use Xditn\Base\modules\Domain\Support\Request\AliyunRequest;
use Xditn\Base\modules\Domain\Support\Request\QCloudRequest;
use Xditn\Base\modules\Domain\Support\Request\Request;

class ApiDomain
{
    /**
     * @param  string  $type
     * @return Request
     */
    public static function getRequest(string $type): Request
    {
        if ($type === 'aliyun') {
            return new AliyunRequest();
        } else {
            return new QCloudRequest();
        }

    }
}
