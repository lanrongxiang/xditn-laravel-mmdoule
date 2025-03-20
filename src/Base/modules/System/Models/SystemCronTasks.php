<?php

declare(strict_types=1);

namespace Xditn\Base\modules\System\Models;

use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $name
 * @property $command
 * @property $cycle
 * @property $days
 * @property $start_at
 * @property $end_at
 * @property $is_schedule
 * @property $is_overlapping
 * @property $is_on_one_server
 * @property $is_run_background
 * @property $run_at
 * @property $run_end_at
 * @property $success_times
 * @property $failed_times
 * @property $status
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class SystemCronTasks extends Model
{
    protected $table = 'system_cron_tasks';

    protected $fillable = ['id', 'name', 'command', 'cycle', 'days', 'start_at', 'end_at', 'is_schedule', 'is_overlapping', 'is_on_one_server', 'is_run_background', 'run_at', 'run_end_at', 'success_times', 'failed_times', 'status', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    protected array $fields = ['id', 'name', 'command', 'cycle', 'days', 'start_at', 'end_at', 'run_at', 'run_end_at', 'success_times', 'failed_times', 'status', 'is_schedule', 'is_overlapping', 'is_on_one_server', 'is_run_background', 'created_at'];

    protected array $form = ['name', 'command', 'cycle', 'days', 'start_at', 'end_at', 'is_schedule', 'is_overlapping', 'is_on_one_server', 'is_run_background'];

    public array $searchable = [
        'name' => 'like',
        'command' => 'like',
    ];


    // 耗时
    protected $appends = ['consuming'];

    // 调度
    public const SCHEDULE = 1;

    public const UN_SCHEDULE = 2;

    // 任务重复
    public const OVERLAPPING = 1;

    public const UN_OVERLAPPING = 2;

    // 同一台服务器执行
    public const ONE_SERVER = 1;

    public const UN_ONE_SERVER = 2;

    // 状态
    public const UN_RUNNING = 1;

    public const RUNNING = 2;

    // 状态
    public const SUCCESS = 1;

    public const FAILED = 2;

    /**
     * 获取可调度的任务
     */
    public function getScheduleTasks()
    {
        return $this->where('is_schedule', self::SCHEDULE)->get();
    }

    /**
     * 是否调度
     */
    public function isSchedule(): bool
    {
        return $this->is_schedule === self::SCHEDULE;
    }

    /**
     * 是否重复
     */
    public function isOverlapping(): bool
    {
        return $this->is_overlapping === self::OVERLAPPING;
    }

    /**
     * 是否在同一台服务器
     */
    public function isOnOneServer(): bool
    {
        return $this->is_on_one_server === self::ONE_SERVER;
    }

    /**
     * 耗时
     */
    public function getConsumingAttribute(): mixed
    {
        return $this->run_end_at > $this->run_at ?

            $this->run_end_at - $this->run_at : 0;
    }
}
