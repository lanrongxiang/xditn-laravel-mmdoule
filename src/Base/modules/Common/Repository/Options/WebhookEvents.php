<?php

namespace Xditn\Base\modules\Common\Repository\Options;

class WebhookEvents implements OptionInterface
{
    public function get(): array
    {
        return [
            [
                'label' => '异常事件',
                'value' => 'exception',
            ],
            [
                'label' => '请求超时',
                'value' => 'connector_response_timeout',
            ],
            [
                'label' => '请求错误',
                'value' => 'connector_request_error',
            ],
            [
                'label' => '请求IP异常',
                'value' => 'connector_ip_exception',
            ],
            [
                'label' => '请求超量',
                'value' => 'connector_request_max',
            ],
        ];
    }
}
