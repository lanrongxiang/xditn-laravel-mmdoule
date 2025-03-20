<?php

namespace Xditn\Base\modules\Domain\Support\Request;

use Illuminate\Support\Facades\Http;
use Xditn\Base\modules\Domain\Support\Sign\Aliyun;
use Xditn\Exceptions\FailedException;

class AliyunRequest implements Request
{
    protected string $baseUrl = 'https://alidns.cn-hangzhou.aliyuncs.com';

    public function whois(string $domain): array
    {
        // TODO: Implement show() method.
        $whois = $this->post('DescribeDomainInfo', [
            'DomainName' => $domain,
        ]);

        return [
            'name_servers' => implode('，', $whois['DnsServers']['DnsServer']),
            'created_at' => strtotime($whois['CreateTime']),
            'expired_at' => strtotime($whois['ExpirationDate'] ?? 0),
        ];
    }

    public function getList(string $domain, $offset, $limit)
    {
        // TODO: Implement getList() method.
        $domainRecords = $this->post('DescribeDomainRecords', [
            'DomainName' => $domain,
            'PageNumber' => $offset ?: 1,
            'PageSize' => $limit,
        ]);

        $records = $domainRecords['DomainRecords']['Record'];
        foreach ($records as &$record) {
            $record = array_change_key_case($record);

            $record['updatedon'] = date('Y-m-d H:i', $record['updatetimestamp'] / 1000);

            $record['name'] = $record['rr'];

            $record['remark'] = '--';
        }

        return [$records, $domainRecords['TotalCount']];
    }

    public function show($recordId, string $domain)
    {
        // TODO: Implement show() method.
        $data = $this->post('DescribeDomainRecordInfo', [
            'RecordId' => $recordId,
        ]);

        return [
            'type' => $data['Type'],
            'value' => $data['Value'],
            'name' => $data['RR'],
            'ttl' => $data['TTL'],
            'remark' => '',
        ];
    }

    public function store(array $data): array
    {
        // TODO: Implement store() method.
        return $this->post('AddDomainRecord', [
            'DomainName' => $data['domain'],
            'Type' => $data['type'],
            'Value' => $data['value'],
            'RR' => $data['name'],
            'TTL' => $data['ttl'],
        ]);
    }

    public function update($recordId, array $data): array
    {
        // TODO: Implement update() method.
        return $this->post('UpdateDomainRecord', [
            'RecordId' => $recordId,
            'Type' => $data['type'],
            'Value' => $data['value'],
            'RR' => $data['name'],
            'TTL' => $data['ttl'],
        ]);
    }

    public function destroy(string $recordId, string $domain)
    {
        // TODO: Implement destroy() method.
        return $this->post('DeleteDomainRecord', [
            'RecordId' => $recordId,
        ]);
    }

    public function get(array $params)
    {
        $headers = $this->getHeaders($params['Action']);

        $headers = (new Aliyun($headers, $params))->authorization('GET');

        return Http::withHeaders($headers)->get($this->baseUrl, $params)->json();
    }

    public function post(string $action, array $params)
    {
        $headers = (new Aliyun($this->getHeaders($action), $params))->authorization('POST');

        // 这里 post 请求的数据一定要设置成 null，不然验签无法通过
        $response = Http::withHeaders($headers)->withQueryParameters($params)->post($this->baseUrl, null)->json();

        return $this->warpResponse($response);
    }

    /**
     * @param  string  $action
     * @return array
     */
    protected function getHeaders(string $action): array
    {
        date_default_timezone_set('UTC');

        return [
            'host' => str_replace('https://', '', $this->baseUrl),
            'x-acs-action' => $action,
            'x-acs-version' => '2015-01-09',
            'x-acs-date' => date('Y-m-d\TH:i:s\Z'),
            'x-acs-signature-nonce' => md5(uniqid().uniqid(md5(microtime(true)), true)),
            'accept' => 'application/json',
        ];
    }

    /**
     * @param  array  $response
     * @return array
     */
    protected function warpResponse(array $response): array
    {
        if (array_key_exists('Code', $response)) {
            throw new FailedException($response['Code']);
        }

        return $response;
    }
}
