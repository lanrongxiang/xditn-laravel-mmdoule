<?php

namespace Xditn\Base\modules\System\Support\Webhook;

abstract class Platform implements WebhookInterface
{
    protected int $timestamp;

    protected string $webhook;

    protected string $secret;

    /**
     * @param  string  $webhook
     * @param  string  $secret
     * @return $this
     */
    public function config(string $webhook, string $secret): static
    {
        $this->webhook = $webhook;

        $this->secret = $secret;

        return $this;
    }
}
