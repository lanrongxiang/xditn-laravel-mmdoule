<?php

namespace Xditn\Base\modules\System\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Xditn\Base\modules\System\Models\ConnectorLog;

class StatisticsConnectorLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics:connector:log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '接口日志统计, 每日统计一次';

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
        $pathRequests = $this->pathRequests();
    }

    public function pathRequests(): array
    {
        $start = Carbon::yesterday()->startOfDay()->timestamp;

        $end = Carbon::yesterday()->endOfDay()->timestamp;

        $pathRequests = [];

        ConnectorLog::whereBetween('created_at', [$start, $end])
            ->cursor()
            ->each(function ($request) use (&$pathRequests) {
                if (isset($pathRequests[$request['path']])) {
                    $pathRequests[$request['path']]['count'] += 1;
                    $pathRequests[$request['path']]['time_taken'] += $request['time_taken'];
                } else {
                    $pathRequests[$request['path']] = [
                        'path' => $request['path'],
                        'count' => 1,
                        'time_token' => $request['time_taken'],
                    ];
                }
            });

        return array_values($pathRequests);
    }
}
