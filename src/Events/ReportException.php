<?php

namespace Xditn\Events;

use Exception;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ReportException
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Exception|Throwable $exception;

    public function __construct(Exception|Throwable $exception)
    {
        $this->exception = $exception;
    }
}
