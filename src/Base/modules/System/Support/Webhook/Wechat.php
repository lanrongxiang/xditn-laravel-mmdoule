<?php

namespace Xditn\Base\modules\System\Support\Webhook;

use Illuminate\Support\Facades\Http;
use Xditn\Exceptions\FailedException;

class Wechat extends Platform
{
    public function sign(): string
    {
        return '';
    }

    public function send(string $msgType, string $content): bool
    {
        $postData = [
            'msgtype' => $msgType,
            $msgType => [
                'content' => $content,
            ],
        ];
        $response = Http::asJson()->post($this->webhook, $postData);

        if (! $response->ok()) {
            throw new FailedException('企微 Webhook 推送失败');
        }

        if (! $response['errcode']) {
            return true;
        }

        throw new FailedException('企微 Webhook 错误: '.$response['errmsg']);
    }
}
