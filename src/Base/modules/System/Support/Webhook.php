<?php

namespace Xditn\Base\modules\System\Support;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;
use Xditn\Base\modules\System\Models\Webhooks;
use Xditn\Base\modules\System\Support\Webhook\DingTalk;
use Xditn\Base\modules\System\Support\Webhook\Feishu;
use Xditn\Base\modules\System\Support\Webhook\WebhookInterface;
use Xditn\Base\modules\System\Support\Webhook\Wechat;
use Xditn\Exceptions\FailedException;
use Xditn\Exceptions\WebhookException;

class Webhook
{
    protected array $values = [];

    public function __construct(
        protected Collection|Webhooks $webhook
    ) {

    }

    public function send(string $content = ''): bool
    {
        try {
            if ($this->webhook instanceof Collection) {
                $webhooks = $this->webhook;
                $webhooks->each(function (Webhooks $webhook) use ($content) {
                    $content = $content ?: $webhook->content;

                    $this->webhook = $webhook;

                    $this->sendThroughPlatform($content);
                });

                return true;
            }
            $content = $content ?: $this->webhook->content;

            return $this->sendThroughPlatform($content);
        } catch (Throwable $e) {
            throw new WebhookException($e->getMessage());
        }
    }

    protected function sendThroughPlatform(string $content): bool
    {
        $content = $this->getContent($content);

        $platform = $this->getWebhookPlatform();

        $platformName = class_basename($platform);

        if (! $content) {
            Log::error("Webhook 平台[{$platformName}]内容为空，发送失败");

            return false;
        }

        return $platform->send($this->webhook->msg_type, $content);
    }

    /**
     * @return mixed
     */
    protected function getContent(string $content): string
    {
        if (count($this->values)) {
            preg_match_all('/{.*?}/', $content, $matches);

            if (count($matches)) {
                $content = Str::of($content)->replace($matches[0], $this->values)->toString();
            }
        }

        return $content;
    }

    protected function getWebhookPlatform(): WebhookInterface
    {
        $platform = [
            Webhooks::DINGTALK => DingTalk::class, // 钉钉
            Webhooks::FEISHU => Feishu::class, // 飞书
            Webhooks::WECHAT => Wechat::class, // 企微
        ][$this->webhook->platform] ?? null;

        if (! $platform) {
            throw new FailedException('The platform dont support now.');
        }

        return app($platform)->config($this->webhook->webhook, $this->webhook->secret);
    }

    /**
     * 设置模版变量的值
     *
     * @return $this
     */
    public function setValues(array|string $values): static
    {
        $this->values = is_string($values) ? [$values] : $values;

        return $this;
    }
}
