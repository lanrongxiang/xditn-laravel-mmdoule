<?php

namespace Xditn\Base\modules\System\Support;

use Illuminate\Console\Scheduling\Schedule as ScheduleConsole;
use Illuminate\Support\Str;
use Xditn\Base\modules\System\Events\CronTaskFailed;
use Xditn\Base\modules\System\Events\CronTaskSuccess;
use Xditn\Base\modules\System\Models\SystemCronTasks;
use Xditn\Base\modules\System\Models\SystemCronTasksLog;

class Schedule
{
    public static function run(ScheduleConsole $schedule): void
    {
        $scheduleTaskModel = new SystemCronTasks();

        $tasks = $scheduleTaskModel->getScheduleTasks();

        foreach ($tasks as $task) {
            $event = $schedule->command($task->command);

            $cycle = Str::of($task->cycle);

            if ($cycle->exactly('dailyAt')) {
                $event = $event->dailyAt($task->start_at ?: '00:00');
            } elseif ($cycle->exactly('weeklyOn')) {
                $event = $event->weeklyOn($task->days ?: 1, $task->start_at ?: '00:00');
            } elseif ($cycle->exactly('monthlyOn')) {
                $event = $event->monthlyOn($task->days ?: 1, $task->start_at ?: '00:00');
            } elseif ($cycle->exactly('lastDayOfMonth')) {
                $event = $event->lastDayOfMonth($task->start_at ?: '00:00');
            } elseif ($cycle->exactly('quarterlyOn')) {
                $event = $event->quarterlyOn($task->days ?: 0, $task->start_at ?: '00:00');
            } else {
                $event = $event->{$cycle->toString()}();
                if ($task->start_at && $task->end_at) {
                    $event = $event->between($task->start_at, $task->end_at);
                }
            }

            if (! $task->isOverlapping()) {
                $event = $event->withoutOverlapping();
            }

            if ($task->isOnOneServer()) {
                $event = $event->onOneServer();
            }

            $taskId = $task->id;
            // 任务开始前
            $event->before(function () use ($taskId) {
                SystemCronTasks::where('id', $taskId)->update([
                    'status' => SystemCronTasks::RUNNING,
                    'run_at' => time(),
                ]);

                // 日志记录
                app(SystemCronTasksLog::class)->insert([
                    'task_id' => $taskId,
                    'start_at' => time(),
                    'end_at' => 0,
                    'created_at' => time(),
                ]);
                // 任务成功
            })->onSuccess(function () use ($taskId) {
                SystemCronTasks::where('id', $taskId)
                    ->increment('success_times', 1, [
                        'status' => SystemCronTasks::UN_RUNNING,
                        'run_end_at' => time(),
                    ]);

                // 定时任务日志
                $log = SystemCronTasksLog::where('task_id', $taskId)->orderByDesc('id')->first();
                $log->status = SystemCronTasks::SUCCESS;
                $log->end_at = time();
                $log->save();

                // 监听时间
                CronTaskSuccess::dispatch($taskId);
                // 任务失败
            })->onFailure(function () use ($taskId) {
                SystemCronTasks::where('id', $taskId)
                    ->increment('failed_times', 1, [
                        'status' => SystemCronTasks::UN_RUNNING,
                        'run_end_at' => time(),
                    ]);
                // 定时任务日志
                $log = SystemCronTasksLog::where('task_id', $taskId)->orderByDesc('id')->first();
                $log->status = SystemCronTasks::FAILED;
                $log->end_at = time();
                $log->save();

                // 监听事件
                CronTaskFailed::dispatch($taskId);
            })->runInBackground();
        }
    }
}
