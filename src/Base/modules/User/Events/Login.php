<?php

namespace Xditn\Base\modules\User\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Xditn\Base\modules\User\Models\User;

class Login
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Request $request;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public ?User $user,
        public ?string $token = null
    ) {
        $this->request = request();
    }
}
