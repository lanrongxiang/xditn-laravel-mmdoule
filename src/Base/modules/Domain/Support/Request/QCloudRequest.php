<?php

namespace Xditn\Base\modules\Domain\Support\Request;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Xditn\Base\modules\Domain\Support\Sign\QCloud;
use Xditn\Exceptions\FailedException;

class QCloudRequest implements Request
{
    protected string $baseUrl = 'https://dnspod.tencentcloudapi.com';

    /**
     * @param  string  $domain
     * @return array
     *
     * @throws ConnectionException
     */
    public function whois(string $domain): array
    {
        // TODO: Implement show() method.
        $info = $this->post('DescribeDomainWhois', ['Domain' => $domain])['Info'];

        return [
            'name_servers' => implode('，', $info['NameServers']),
            'created_at' => $info['CreationDate'],
            'expired_at' => strtotime($info['ExpirationDate']),
        ];
    }

    public function getList(string $domain, $offset = 0, $limit = 10): array
    {
        $data = $this->post('DescribeRecordList', [
            'Domain' => $domain,
            'Offset' => intval($offset),
            'Limit' => intval($limit),
        ]);

        $records = $data['RecordList'];
        $total = $data['RecordCountInfo']['TotalCount'];

        foreach ($records as &$record) {
            $record = array_change_key_case($record);
        }

        return [$records, $total];
    }

    public function show($recordId, string $domain)
    {
        $data = $this->post('DescribeRecord', [
            'Domain' => $domain,
            'RecordId' => intval($recordId),
        ]);

        return [
            'type' => $data['RecordInfo']['RecordType'],
            'value' => $data['RecordInfo']['Value'],
            'name' => $data['RecordInfo']['SubDomain'],
            'ttl' => $data['RecordInfo']['TTL'],
            'remark' => $data['RecordInfo']['Remark'],
        ];
    }

    /**
     * 保存
     *
     * @param  array  $data
     * @return mixed
     */
    public function store(array $data): mixed
    {
        // TODO: Implement store() method.
        return $this->post('CreateRecord', [
            'Domain' => $data['domain'],
            'RecordType' => $data['type'],
            'RecordLine' => $data['line'] ?? '默认',
            'Value' => $data['value'],
            'SubDomain' => $data['name'],
            'TTL' => $data['ttl'],
            'Remark' => $data['remark'] ?? '',
        ]);
    }

    /**
     * 更新
     *
     * @param  array  $data
     * @return mixed
     *
     * @throws ConnectionException
     */
    public function update($id, array $data): mixed
    {
        // TODO: Implement update() method.
        return $this->post('ModifyRecord', [
            'Domain' => $data['domain'],
            'RecordType' => $data['type'],
            'RecordLine' => $data['line'] ?? '默认',
            'Value' => $data['value'],
            'SubDomain' => $data['name'],
            'TTL' => $data['ttl'],
            'Remark' => $data['remark'] ?? '',
            'RecordId' => intval($id),
        ]);
    }

    public function destroy(string $recordId, string $domain)
    {
        // TODO: Implement destroy() method.
        return $this->post('DeleteRecord', [
            'Domain' => $domain,
            'RecordId' => intval($recordId),
        ]);
    }

    /**
     * @param  string  $action
     * @param  array  $params
     * @return mixed
     */
    public function get(string $action, array $params)
    {
        $headers = $this->getHeaders($action, 'application/x-www-form-urlencoded');

        $headers['Authorization'] = (new Qcloud($headers['X-TC-Timestamp'], $params))->authorization(__FUNCTION__);

        $response = Http::withHeaders($headers)->get($this->baseUrl, $params);

        return $this->warpResponse($response->json());
    }

    /**
     * @param  string  $action
     * @param  array  $params
     * @return mixed
     *
     * @throws ConnectionException
     */
    public function post(string $action, array $params)
    {
        $headers = $this->getHeaders($action, 'application/json');

        $headers['Authorization'] = (new Qcloud($headers['X-TC-Timestamp'], $params))->authorization(__FUNCTION__);

        $response = Http::withHeaders($headers)->asJson()->post($this->baseUrl, $params);

        return $this->warpResponse($response->json());
    }

    /**
     * @param  string  $action
     * @param  string  $contentType
     * @return array
     */
    protected function getHeaders(string $action, string $contentType): array
    {
        return [
            'X-TC-Action' => $action,
            'X-TC-Version' => '2021-03-23',
            'X-TC-Timestamp' => time(),
            'Content-Type' => $contentType,
        ];
    }

    /**
     * @param  array  $response
     * @return mixed
     */
    protected function warpResponse(array $response): mixed
    {
        if (array_key_exists('Error', $response['Response'])) {
            throw new FailedException($response['Response']['Error']['Message']);
        }

        return $response['Response'];
    }
}
