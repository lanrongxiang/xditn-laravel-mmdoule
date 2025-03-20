<?php

namespace Xditn\Base\modules\Wechat\Support\Official;

use EasyWeChat\OfficialAccount\Application;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Throwable;
use Xditn\Exceptions\FailedException;

abstract class Official
{
    // protected Application $app;

    protected string $baseURL = 'https://api.weixin.qq.com/cgi-bin/';

    protected function getApp(): Application
    {
        return new Application(config('wechat.official'));
    }

    protected function post(string $path, array|Collection $data): array
    {
        try {
            if ($data instanceof Arrayable) {
                $data = $data->toArray();
            }

            return $this->getApp()->getClient()->post($this->getApi($path), $data)->toArray();
        } catch (Throwable $e) {
            throw new FailedException($e->getMessage());
        }
    }

    protected function postJson(string $path, array|Collection $data): array
    {
        try {
            if ($data instanceof Arrayable) {
                $data = $data->toArray();
            }

            return $this->getApp()->getClient()->postJson($this->getApi($path), $data)->toArray();
        } catch (Throwable $e) {
            throw new FailedException($e->getMessage());
        }
    }

    protected function get(string $path, array|Collection $options = []): array
    {
        try {
            if ($options instanceof Arrayable) {
                $options = $options->toArray();
            }

            return $this->getApp()->getClient()->get($this->getApi($path), $options)->toArray();
        } catch (Throwable $e) {
            throw new FailedException($e->getMessage());
        }
    }

    protected function getApi(string $path): string
    {
        return $this->baseURL.$path;
    }
}
