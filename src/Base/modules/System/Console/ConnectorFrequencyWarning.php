<?php

namespace Xditn\Base\modules\System\Console;

use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;
use Xditn\Base\modules\System\Models\ConnectorLog;
use Xditn\Base\modules\System\Models\Webhooks;
use Xditn\Base\modules\System\Support\Webhook;

class ConnectorFrequencyWarning extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'connector:Frequency:warning';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '接口告警统计';

    /**
     * 日志记录
     */
    protected array $logs = [];

    /**
     * 批量插入条数限制
     */
    protected int $limit = 100;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->responseTimeout();

        $this->requestError();

        $this->ipException();

        $this->requestsMore();
    }

    /**
     * 响应超时监控
     */
    protected function responseTimeout(): void
    {
        if (! config('connector.timeout.enable')) {
            return;
        }

        $frequency = config('connector.timeout.frequency');

        $timeout = config('connector.timeout.time');

        $rate = config('connector.timeout.rate');

        $total = $timeoutRequests = 0;

        $this->getConnectorLogs($frequency)
            ->each(function ($item) use ($timeout, &$total, &$timeoutRequests) {
                if ($item['time_taken'] >= $timeout) {
                    $timeoutRequests++;
                }

                $total++;
            });

        $currentRate = sprintf('%.2f', $timeoutRequests / $total) * 100;
        if ($currentRate >= $rate) {
            // 提醒
            $webhook = new Webhook(Webhooks::connectorRequestTimeout());

            $webhook->setValues([$frequency, $timeout, $rate])->send();
        }
    }

    /**
     * 请求错误提醒
     */
    protected function requestError(): void
    {
        if (! config('connector.request_error.enable')) {
            return;
        }

        $frequency = config('connector.request_error.frequency');

        $codes = config('connector.request_error.http_status');

        $rate = config('connector.request_error.rate');

        $total = $requestErrors = 0;

        $this->getConnectorLogs($frequency)
            ->each(function ($item) use ($codes, &$total, &$requestErrors) {
                if (in_array($item['status_code'], $codes)) {
                    $requestErrors++;
                }

                $total++;
            });

        $currentRate = sprintf('%.2f', $requestErrors / $total) * 100;
        if ($currentRate >= $rate) {
            // 提醒
            $webhook = new Webhook(Webhooks::connectorRequestError());

            $webhook->setValues([$frequency, implode('|', $codes), $rate])->send();
        }
    }

    /**
     * ip 异常提醒
     */
    protected function ipException(): void
    {
        if (! config('connector.ip_exception.enable')) {
            return;
        }

        $frequency = config('connector.ip_exception.frequency');

        $exceptIps = explode(',', config('connector.ip_exception.ips'));

        $rate = config('connector.ip_exception.rate');

        $total = 0;

        $ipsRequests = [];

        $this->getConnectorLogs($frequency)
            ->each(function ($item) use ($exceptIps, &$ipsRequests, &$total) {
                if (! in_array($item['ip'], $exceptIps)) {
                    if (isset($ipsRequests[$item['ip']])) {
                        $ipsRequests[$item['ip']] += 1;
                    } else {
                        $ipsRequests[$item['ip']] = 1;
                    }
                }

                $total++;
            });

        $exceptionIps = [];
        foreach ($ipsRequests as $ip => $count) {
            $currentRate = sprintf('%.2f', $count / $total) * 100;
            if ($currentRate >= $rate) {
                $exceptionIps[] = $ip;

            }
        }

        if (count($exceptionIps)) {
            // 提醒
            $webhook = new Webhook(Webhooks::connectorIpException());

            $webhook->setValues([$frequency, implode('|', $exceptionIps)])->send();
        }

    }

    /**
     * 请求量监控提醒
     */
    protected function requestsMore(): void
    {
        if (config('connector.requests.enable')) {
            return;
        }

        $maxRequest = config('connector.requests.max');
        $frequency = config('connector.requests.frequency');

        if (
            $this->getConnectorLogs($frequency)
                ->count() >= $maxRequest
        ) {
            // 提醒
            $webhook = new Webhook(Webhooks::connectorIpException());

            $webhook->setValues([$frequency, $maxRequest])->send();
        }
    }

    protected function getConnectorLogs($frequency): LazyCollection
    {
        $currentTime = time();

        $startTime = $currentTime - $frequency * 60;

        return ConnectorLog::query()
            ->whereBetween('created_at', [$startTime, $currentTime])
            ->cursor();
    }
}
