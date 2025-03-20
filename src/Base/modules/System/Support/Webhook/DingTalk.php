<?php

namespace Xditn\Base\modules\System\Support\Webhook;

use Illuminate\Support\Facades\Http;
use Xditn\Exceptions\FailedException;

class DingTalk extends Platform
{
    public function send(string $msgType, string $content): bool
    {
        $this->timestamp = intval(microtime(true) * 1000);

        // TODO: Implement send() method.
        $query['timestamp'] = $this->timestamp;
        if ($this->secret) {
            $query['sign'] = $this->sign();
        }

        $response = Http::asJson()->post(
            sprintf('%s&%s', $this->webhook, http_build_query($query)),
            $this->{$msgType}($content)
        );

        if (! $response->ok()) {
            throw new FailedException('钉钉 Webhook 推送失败');
        }

        if (! $response['errcode']) {
            return true;
        }

        throw new FailedException('钉钉 Webhook 错误: '.$response['errmsg']);
    }

    protected function text(string $content): array
    {
        return [
            'msgtype' => 'text',
            'text' => $content,
        ];
    }

    protected function markdown(string $content): array
    {
        return [
            'msgtype' => 'markdown',
            'markdown' => [
                'text' => $content,
                'title' => 'markdown 消息',
            ],
        ];
    }

    /**
     * sign
     */
    public function sign(): string
    {
        // TODO: Implement sign() method.
        return urlencode(base64_encode(hash_hmac(
            'sha256',
            $this->timestamp."\n".$this->secret,
            $this->secret,
            true
        )));
    }
}
