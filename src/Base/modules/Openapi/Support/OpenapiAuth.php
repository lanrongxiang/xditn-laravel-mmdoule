<?php

namespace Xditn\Base\modules\Openapi\Support;

use Illuminate\Support\Facades\Context;
use Xditn\Base\modules\Openapi\Exceptions\InvalidAppKeyException;
use Xditn\Base\modules\Openapi\Exceptions\InvalidTimestampException;
use Xditn\Base\modules\Openapi\Models\OpenapiRequestLog;
use Xditn\Base\modules\Openapi\Models\Users;

/**
 * 签名校验
 */
class OpenapiAuth
{
    protected ?Users $user = null;

    /**
     * check
     *
     * @return bool
     */
    public function check(string $appKey, string $signature, array $params)
    {
        // 写入日志
        app(OpenapiRequestLog::class)->storeBy([
            'app_key' => $appKey,
            'data' => json_encode($params),
            'request_id' => Context::get('openapi_request_id'),
        ]);

        // 检查时间戳
        if (! isset($params['timestamp']) || ! $this->validateTimestampPeriod($params['timestamp'])) {
            throw new InvalidTimestampException;
        }

        $user = Users::query()->where('app_key', $appKey)->firstOr(function () {
            throw new InvalidAppKeyException;
        });

        $params = $this->flattenArray($params);

        ksort($params);

        $signStr = '';
        foreach ($params as $key => $value) {
            $signStr .= $key.'='.$value.'&';
        }

        if ($signature == hash_hmac('sha256', rtrim($signStr, '&'), $user->app_secret)) {
            $this->user = $user;

            return true;
        }

        return false;
    }

    /**
     * 扁平化数组
     */
    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if ($this->isAssociativeArray($value)) {
                    $result = array_merge($result, $this->flattenArray($value, $prefix.$key.'.'));
                } else {
                    foreach ($value as $index => $item) {
                        $result = array_merge($result, $this->flattenArray($item, $prefix.$key.'['.$index.'].'));
                    }
                }
            } else {
                $result[$prefix.$key] = $value;
            }
        }

        return $result;
    }

    private function isAssociativeArray($array): bool
    {
        if ($array === []) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    protected function validateTimestampPeriod(int $timestamp): bool
    {
        return abs(time() - $timestamp) <= config('api.timestamp_period', 60);
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function getUserId(): int
    {
        return $this->user->id;
    }
}
