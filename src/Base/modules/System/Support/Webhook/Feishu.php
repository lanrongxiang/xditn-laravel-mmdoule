<?php

namespace Xditn\Base\modules\System\Support\Webhook;

use Illuminate\Support\Facades\Http;
use Xditn\Exceptions\FailedException;

class Feishu extends Platform
{
    public function sign(): string
    {
        // TODO: Implement sign() method.
        return base64_encode(hash_hmac(
            'sha256',
            '',
            $this->timestamp."\n".$this->secret,
            true
        ));
    }

    public function send(string $msgType, string $content): bool
    {
        // TODO: Implement send() method.
        $this->timestamp = time();

        $postData = [
            'timestamp' => $this->timestamp,
            'msg_type' => $msgType,
            'content' => [
                $msgType => $content,
            ],
        ];

        // 签名校验
        if ($this->secret) {
            $postData['sign'] = $this->sign();
        }

        $response = Http::asJson()->post($this->webhook, $postData);

        if (! $response->ok()) {
            throw new FailedException('飞书 Webhook 推送失败');
        }

        if (! $response['code']) {
            return true;
        }

        throw new FailedException('飞书 Webhook 错误: '.$response['msg']);
    }
}
