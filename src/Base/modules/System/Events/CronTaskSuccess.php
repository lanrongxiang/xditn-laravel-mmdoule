<?php

namespace Xditn\Base\modules\System\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CronTaskSuccess
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * 创建一个新的事件实例。
     */
    public function __construct(public int $taskId)
    {
    }
}
