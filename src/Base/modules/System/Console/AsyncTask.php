<?php

namespace Xditn\Base\modules\System\Console;

use Illuminate\Console\Command;
use Xditn\Base\modules\System\Models\AsyncTask as AsyncTaskModel;
use Xditn\Contracts\AsyncTaskInterface;

class AsyncTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'async:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Async task';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        ini_set('memory_limit', config('system.task.memory_limit'));

        set_time_limit(config('system.task.timeout'));

        if ($this->hasRunningTask()) {
            $taskModel = new AsyncTaskModel();
            $unRunningTask = $taskModel->getUnRunning();

            if (! $unRunningTask) {
                return;
            }

            $unRunningTask->start();
            try {
                /* @var AsyncTaskInterface $class */
                $class = app($unRunningTask->task);
                $params = json_decode($unRunningTask->params, true);
                $result = $class->run($params ?: []);
                $unRunningTask->finished($result);
            } catch (\Throwable|\Exception $e) {
                $unRunningTask->error(sprintf('错误:%s, 在文件 %s 的 %d 行', $e->getMessage(), $e->getFile(), $e->getLine()));
            }
        }
    }

    public function hasRunningTask(): bool
    {
        return app(AsyncTaskModel::class)->getRunningTaskNum() < 3;
    }
}
