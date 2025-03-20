<?php

namespace Xditn\Base\modules\System\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Xditn\Base\modules\System\Models\ConnectorLog;

class ConnectorLogRecord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'connector:log:record';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '接口日志分析';

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
        while (true) {
            if ($log = Redis::rPop(ConnectorLog::QUEUE_LOG_NAME)) {
                $this->logs[] = json_decode($log, true);
            } else {
                break;
            }

            if (count($this->logs) >= $this->limit) {
                $this->save();
            }
        }

        if (count($this->logs)) {
            $this->save();
        }
    }

    /**
     * 清空
     *
     * @return void
     */
    protected function empty()
    {
        $this->logs = [];
    }

    /**
     * 保存日志
     *
     * @return void
     */
    public function save()
    {
        try {
            ConnectorLog::query()->insert($this->logs);
            $this->empty();
        } catch (\Throwable|\Exception $e) {
            Log::error('接口日志消费异常：'.$e->getMessage());
            $this->rollbackToQueue();
        }
    }

    /**
     * 错误回滚日志到队列
     *
     * @return void
     */
    protected function rollbackToQueue(): void
    {
        foreach ($this->logs as $log) {
            Redis::lPush(ConnectorLog::QUEUE_LOG_NAME, json_encode($log));
        }

        $this->empty();
    }
}
