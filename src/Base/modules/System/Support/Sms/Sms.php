<?php

namespace Xditn\Base\modules\System\Support\Sms;

use Overtrue\EasySms\EasySms;
use Xditn\Base\modules\System\Models\SystemSmsTemplate;
use Xditn\Exceptions\FailedException;

abstract class Sms
{
    abstract protected function config(): array;

    abstract protected function gateway(): string;

    abstract protected function send(string $template, string $mobile, array $templateData = []): bool;

    protected function getGateway(): EasySms
    {
        $config = [
            'timeout' => 5.0,
            'default' => [
                'gateways' => [$this->gateway()],
            ],
            'gateways' => [
                'errorlog' => [
                    'file' => storage_path('logs/sms.log'),
                ],
                $this->gateway() => $this->config(),
            ],
        ];

        return new EasySms($config);
    }

    protected function getTemplateBy(string $identify): SystemSmsTemplate
    {
        return SystemSmsTemplate::where('channel', $this->gateway())
            ->where('identify', $identify)->firstOr('*', function () {
                throw new FailedException('模版未找到，请确认是否添加?');
            });
    }
}
