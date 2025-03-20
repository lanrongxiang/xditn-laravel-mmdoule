<?php

namespace Xditn\Base\modules\Common\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 上传成功后的事件
 */
class UploadedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param  array{ext: string, category_id: int,path: string,originalName: string,size: int,type: string, driver: string}  $uploadInfo
     */
    public function __construct(public array $uploadInfo)
    {
    }
}
