<?php

namespace Xditn\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 用户事件类
 */
class User
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Authenticatable|Model $user;

    /**
     * 构造函数，初始化用户实例
     */
    public function __construct(Authenticatable|Model $user)
    {
        $this->user = $user;
    }
}
