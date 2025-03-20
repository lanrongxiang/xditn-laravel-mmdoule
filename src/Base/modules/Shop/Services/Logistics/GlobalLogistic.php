<?php

namespace Xditn\Base\modules\Shop\Services\Logistics;

// 全球物流查询
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Xditn\Exceptions\FailedException;

class GlobalLogistic extends Logistic
{
    public function traces(array $params)
    {
        $response = Http::withHeaders([
            'Authorization' => 'APPCODE '.$this->token(),
        ])
            ->get($this->url('gxali'), [
                'n' => $params['order_no'],
            ]);

        if ($response->ok()) {
            return $response->json();
        }

        $statusCode = $response->status();

        throw new FailedException('请求失败: 状态码'.$statusCode.',错误信息:'.$response->header('x-ca-error-message'));
    }

    /**
     * @throws ConnectionException
     */
    public function expressLists()
    {
        $response = Http::withHeaders([
            'Authorization' => 'APPCODE '.$this->token(),
        ])
            ->get($this->url('globalExpressLists'));

        if ($response->ok()) {
            return $response->json();
        }
        $statusCode = $response->status();

        throw new FailedException('请求失败: 状态码'.$statusCode.',错误信息:'.$response->header('x-ca-error-message'));
    }

    protected function url(string $path)
    {
        return config('logistics.global.host').$path;
    }

    protected function token()
    {
        return config('logistics.global.token');
    }
}
