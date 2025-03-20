<?php

declare(strict_types=1);

namespace Xditn\Base\modules\System\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $username
 * @property $path
 * @property $method
 * @property $user_agent
 * @property $ip
 * @property $controller
 * @property $action
 * @property $time_taken
 * @property $status_code
 * @property $creator_id
 * @property $from
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class ConnectorLog extends Model
{
    protected $table = 'system_connector_log';

    protected $fillable = ['id', 'username', 'path', 'method', 'user_agent', 'ip', 'controller', 'action', 'time_taken', 'status_code', 'creator_id', 'from', 'created_at', 'updated_at', 'deleted_at'];

    protected array $fields = ['id', 'username', 'path', 'method', 'user_agent', 'ip', 'controller', 'action', 'time_taken', 'status_code', 'from', 'created_at'];

    public array $searchable = [
        'username' => 'like',

    ];

    const FROM_DASHBOARD = 1;

    const FROM_APP = 2;

    // 记录日志的队列日志 Name
    const QUEUE_LOG_NAME = 'connector_log';

    public function recentlyEveryMinuteRequests($recent = 60): array
    {
        return Cache::remember('connector_log_minute', 60, function () use ($recent) {
            $currentTime = time();
            $startTime = $currentTime - $recent * 60;

            $requests = [];

            $this->where('created_at', '>', $startTime)
                ->where('created_at', '<', $currentTime)
                ->select(['id', 'created_at'])
                ->orderBy('id')
                ->cursor()
                ->each(function ($request) use (&$requests) {
                    $min = date('i');
                    if (isset($request[$min])) {
                        $requests[$min]['count'] += 1;
                    } else {
                        $requests[$min] = [
                            'minute' => $min,
                            'count' => 1,
                        ];
                    }
                });

            return array_values($requests);
        });
    }

    public function recentlyEveryHourRequests($recent = 24): array
    {
        return Cache::remember('connector_log_hourly', 60 * 60, function () use ($recent) {
            $currentTime = time();
            $startTime = $currentTime - $recent * 60 * 60;

            $requests = [];

            $this->where('created_at', '>', $startTime)
                ->where('created_at', '<', $currentTime)
                ->select(['id', 'created_at'])
                ->orderBy('id')
                ->cursor()
                ->each(function ($request) use (&$requests) {
                    $hour = date('H');
                    if (isset($request[$hour])) {
                        $requests[$hour]['count'] += 1;
                    } else {
                        $requests[$hour] = [
                            'hour' => $hour,
                            'count' => 1,
                        ];
                    }
                });

            return array_values($requests);
        });
    }

    public function summary(): array
    {
        $total = $this->count();
        $totalTime = $this->sum('time_taken');
        $not200StatusCodeRequests = $this->where('status_code', '<>', 200)->count();
        $apiCount = $this->select('path')->groupBy('path')->get()->count();

        $averageTime = $total ? sprintf('%.2f', $totalTime / $total) : 0;

        $incorrectRate = 0;
        if ($not200StatusCodeRequests > 0) {
            $incorrectRate = $total ? (sprintf('%.2f', $not200StatusCodeRequests / $total) * 100) : 0;
        }

        return [
            'total' => $total,
            'average_time' => $averageTime,
            'incorrect_rate' => $incorrectRate,
            'api_count' => $apiCount,
        ];
    }

    public function statusCodes(): array
    {
        return [
            [
                'name' => '200',
                'value' => $this->where('status_code', 200)->whereBetween('created_at', $this->timeRange())->count(),
            ],
            [
                'name' => '30x',
                'value' => $this->where('status_code', '>=', 300)->where('status_code', '<', 400)->whereBetween('created_at', $this->timeRange())->count(),
            ],
            [
                'name' => '40x',
                'value' => $this->where('status_code', '>=', 400)->where('status_code', '<', 500)->whereBetween('created_at', $this->timeRange())->count(),
            ],
            [
                'name' => '50x',
                'value' => $this->where('status_code', '>=', 500)->where('status_code', '<', 600)->whereBetween('created_at', $this->timeRange())->count(),
            ],
        ];
    }

    public function timeTaken(): array
    {
        return [
            [
                'name' => '0~100',
                'value' => $this->whereBetween('time_taken', [0, 100])->whereBetween('created_at', $this->timeRange())->count(),
            ],
            [
                'name' => '100~500',
                'value' => $this->whereBetween('time_taken', [101, 500])->whereBetween('created_at', $this->timeRange())->count(),
            ],
            [
                'name' => '500~1000',
                'value' => $this->whereBetween('time_taken', [501, 1000])->whereBetween('created_at', $this->timeRange())->count(),
            ],
            [
                'name' => '1000~3000',
                'value' => $this->whereBetween('time_taken', [1001, 3000])->whereBetween('created_at', $this->timeRange())->count(),
            ],
            [
                'name' => '3000~5000',
                'value' => $this->whereBetween('time_taken', [3001, 5000])->whereBetween('created_at', $this->timeRange())->count(),
            ],
            [
                'name' => '5000+',
                'value' => $this->where('time_taken', '>', 5000)->whereBetween('created_at', $this->timeRange())->count(),
            ],
        ];

    }

    /**
     * 请求量 top15
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|ConnectorLog[]
     */
    public function requestsTop10()
    {
        $requests = [];

        $this->select([
            'path',
            DB::raw('count(*) as count'),
        ])
            ->whereBetween('created_at', $this->timeRange())
            ->groupBy('path')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->each(function ($item) use (&$requests) {
                $requests['pathes'][] = $item['path'];
                $requests['count'][] = $item['count'];
            });

        return $requests;
    }

    /**
     * 请求错误 top15
     */
    public function requestErrorsTop10(): array
    {
        $requests = [];

        $this->select([
            'path',
            DB::raw('count(*) as count'),
        ])
            ->where('status_code', '>=', 300)
            ->whereBetween('created_at', $this->timeRange())
            ->groupBy('path')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->each(function ($item) use (&$requests) {
                $requests['pathes'][] = $item['path'];
                $requests['count'][] = $item['count'];
            });

        return $requests;
    }

    /**
     * 请求最快 top15
     */
    public function requestFastTop10(): array
    {
        $requests = [];

        $this->select([
            'path',
            DB::raw('sum(time_taken) as time_taken'),
            DB::raw('count(*) as count'),
        ])
            ->whereBetween('created_at', $this->timeRange())
            ->groupBy('path')
            ->cursor()
            ->map(function (&$item) use (&$top15) {
                $item->every_time_token = sprintf('%.2f', $item['time_taken'] / $item['count']);

                return $item;
            })
            ->sortByDesc('every_time_token')
            ->take(10)
            ->each(function ($item) use (&$requests) {
                $requests['pathes'][] = $item['path'];
                $requests['count'][] = $item['every_time_token'];
            });

        return $requests;
    }

    /**
     * 请求最慢 top15
     */
    public function requestSlowTop10(): array
    {
        $requests = [];

        $this->select([
            'path',
            DB::raw('sum(time_taken) as time_taken'),
            DB::raw('count(*) as count'),
        ])
            ->whereBetween('created_at', $this->timeRange())
            ->groupBy('path')
            ->cursor()
            ->map(function (&$item) use (&$top15) {
                $item->every_time_token = sprintf('%.2f', $item['time_taken'] / $item['count']);

                return $item;
            })
            ->sortBy('every_time_token')
            ->take(10)
            ->each(function ($item) use (&$requests) {
                $requests['pathes'][] = $item->path;
                $requests['count'][] = $item->every_time_token;
            });

        return $requests;
    }

    /**
     * 每分钟请求数据
     *
     * @return array
     */
    public function everyMinuteRequests(): array
    {
        return Cache::remember('every_minute_requests', 60, function () {

            $currentTime = time();

            $startTime = $currentTime - 60 * 59;
            $requests = [];

            for ($i = 0; $i < 60; $i++) {
                $min = date('i', $startTime + ($i * 60));
                $requests[$min] = [
                    'count' => 0,
                    'time_taken' => 0,
                    'min' => $min,
                ];
            }

            $this->where('created_at', '>=', $startTime)
                ->where('created_at', '<=', $currentTime)
                ->orderBy('id')
                ->select('id', 'time_taken', 'created_at')
                ->cursor()
                ->each(function ($item) use (&$requests) {
                    $min = date('i', $item->created_at->timestamp);
                    if (isset($requests[$min])) {
                        $requests[$min]['count'] += 1;
                        $requests[$min]['time_taken'] += $item['time_taken'];
                    }
                });

            foreach ($requests as &$request) {
                if ($request['count'] > 0) {
                    $request['time_taken'] = sprintf('%.2f', $request['time_taken'] / $request['count']);
                }
            }

            return [
                'minutes' => array_column($requests, 'min'),
                'count' => array_column($requests, 'count'),
                'time_taken' => array_column($requests, 'time_taken'),
            ];
        });
    }

    /**
     * 每小时请求数据
     *
     * @return array
     */
    public function everyHourRequests(): array
    {
        return Cache::remember('every_hour_requests', 60, function () {

            $currentTime = time();

            $startTime = $currentTime - 60 * 60 * 23;

            $requests = [];

            for ($i = 0; $i < 24; $i++) {
                $h = date('H', $startTime + ($i * 60 * 60));
                $requests[$h] = [
                    'hour' => $h,
                    'count' => 0,
                    'time_taken' => 0,
                ];
            }

            $this->where('created_at', '>=', $startTime)
                ->where('created_at', '<=', $currentTime)
                ->orderBy('id')
                ->select('id', 'time_taken', 'created_at')
                ->cursor()
                ->each(function ($item) use (&$requests) {
                    $hour = date('H', $item->created_at->timestamp);
                    if (isset($requests[$hour])) {
                        $requests[$hour]['count'] += 1;
                        $requests[$hour]['time_taken'] += $item['time_taken'];
                    }
                });

            foreach ($requests as &$request) {
                if ($request['count'] > 0) {
                    $request['time_taken'] = sprintf('%.2f', $request['time_taken'] / $request['count']);
                }
            }

            return [
                'hours' => array_column($requests, 'hour'),
                'count' => array_column($requests, 'count'),
                'time_taken' => array_column($requests, 'time_taken'),
            ];
        });
    }

    protected function timeRange(): array
    {
        $time = Request::get('time');

        return [time() - $time, time()];
    }
}
