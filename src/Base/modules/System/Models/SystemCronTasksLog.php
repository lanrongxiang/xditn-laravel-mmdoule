<?php

declare(strict_types=1);

namespace Xditn\Base\modules\System\Models;

use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $task_id
 * @property $start_at
 * @property $end_at
 * @property $status
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class SystemCronTasksLog extends Model
{
    protected $table = 'system_cron_tasks_log';

    protected $fillable = ['id', 'task_id', 'start_at', 'end_at', 'status', 'created_at', 'updated_at', 'deleted_at'];

    protected array $fields = ['id', 'task_id', 'start_at', 'end_at', 'status', 'created_at', 'updated_at'];

    protected $casts = [
        'start_at' => 'date:Y-m-d H:i:s',
        'end_at' => 'date:Y-m-d H:i:s',
    ];

    // 耗时
    protected $appends = ['consuming'];

    public array $searchable = [
        'task_id' => '=',
        'start_at' => '>=',
        'end_at' => '<=',
    ];

    /**
     * 耗时
     */
    public function getConsumingAttribute(): mixed
    {
        return $this->end_at->timestamp > $this->start_at->timestamp ?

            $this->end_at->timestamp - $this->start_at->timestamp : 0;
    }
}
