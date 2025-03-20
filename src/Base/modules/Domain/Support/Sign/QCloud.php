<?php

namespace Xditn\Base\modules\Domain\Support\Sign;

use Xditn\Exceptions\FailedException;

class QCloud
{
    protected string $service = 'dnspod';

    protected string $host = 'dnspod.tencentcloudapi.com';

    protected string $secretId;

    protected string $secretKey;

    protected string $version = '2021-03-23';

    protected string $algorithm = 'TC3-HMAC-SHA256';

    protected string $canonicalUri = '/';

    protected int $timestamp;

    /**
     * @var array
     */
    protected array $params;

    protected array $data;

    protected string $httpMethod;

    protected bool $isPost = false;

    /**
     * Qcloud constructor.
     *
     * @param  int  $time
     * @param  array  $data
     */
    public function __construct(int $time, array $data)
    {
        $this->data = $data;

        if (! $secretId = config('domain.qcloud.appid')) {
            throw new FailedException('腾讯云域名管理 appid 未设置');
        }

        if (! $secretKey = config('domain.qcloud.secret')) {
            throw new FailedException('腾讯云域名管理密钥未设置');
        }

        $this->secretId = $secretId;

        $this->secretKey = $secretKey;

        $this->timestamp = $time;
    }

    /**
     * @return string
     */
    protected function getContentType(): string
    {
        return $this->isPost ? 'application/json' : 'application/x-www-form-urlencoded';
    }

    protected function getCanonicalHeaders(): string
    {
        return sprintf("content-type:%s\n".'host:'.$this->host."\n", $this->getContentType());
    }

    protected function getCanonicalQueryString(): string
    {
        if ($this->isPost) {
            return '';
        } else {
            return http_build_query($this->data);
        }
    }

    protected function getSignHeaders(): string
    {
        return 'content-type;host';
    }

    protected function getHashedRequestPayload(): string
    {
        return hash('sha256', $this->isPost ? json_encode($this->data) : '');
    }

    protected function getString2Sign($hashedCanonicalRequest): string
    {
        return $this->algorithm."\n"
                    .$this->timestamp."\n"
                    .$this->getCredentialScope()."\n"
                    .$hashedCanonicalRequest;
    }

    protected function getDate()
    {
        return gmdate('Y-m-d', $this->timestamp);
    }

    protected function getCredentialScope(): string
    {
        return $this->getDate().'/'.$this->service.'/tc3_request';
    }

    /**
     * @param $string2Sign
     * @return string
     */
    protected function getSignature($string2Sign): string
    {
        $secretDate = hash_hmac('SHA256', $this->getDate(), 'TC3'.$this->secretKey, true);

        $secretService = hash_hmac('SHA256', $this->service, $secretDate, true);

        $secretSigning = hash_hmac('SHA256', 'tc3_request', $secretService, true);

        return hash_hmac('SHA256', $string2Sign, $secretSigning);
    }

    protected function createCanonicalRequest(): string
    {
        return $this->httpMethod."\n"
        .$this->canonicalUri."\n"
        .$this->getCanonicalQueryString()."\n"
        .$this->getCanonicalHeaders()."\n"
        .$this->getSignHeaders()."\n"
        .$this->getHashedRequestPayload();
    }

    public function authorization($method): string
    {
        $this->isPost = strtolower($method) === 'post';
        $this->httpMethod = strtoupper($method);

        // 生成请求字符串
        $canonicalRequest = $this->createCanonicalRequest();
        // 生成要签名的字符串
        $hashedCanonicalRequest = hash('SHA256', $canonicalRequest);
        $stringToSign = $this->getString2Sign($hashedCanonicalRequest);
        // 把字符串进行签名
        $signature = $this->getSignature($stringToSign);

        return $this->algorithm
            .' Credential='.$this->secretId.'/'.$this->getCredentialScope()
            .', SignedHeaders=content-type;host, Signature='.$signature;
    }
}
