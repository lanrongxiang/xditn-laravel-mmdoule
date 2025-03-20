<?php

namespace Xditn\Base\modules\System\Support\Sms;

use Overtrue\EasySms\Exceptions\InvalidArgumentException;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Throwable;
use Xditn\Base\modules\System\Enums\SmsChannel;

class QCloud extends Sms
{
    protected function gateway(): string
    {
        // TODO: Implement gateway() method.
        return SmsChannel::QCLOUD->value();
    }

    public function config(): array
    {
        // TODO: Implement config() method.
        return config('sms.qcloud');
    }

    /**
     * @throws NoGatewayAvailableException
     * @throws Throwable
     * @throws InvalidArgumentException
     */
    public function send(string $template, string $mobile, array $templateData = []): bool
    {
        $template = $this->getTemplateBy($template);

        $this->getGateway()
            ->send($mobile, [
                'template' => $template->template_id,
                'data' => $templateData,
            ]);

        return true;
    }
}
