<?php

namespace Xditn\Base\modules\System\Listeners;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Xditn\Base\modules\System\Events\ConnectorLogEvent;
use Xditn\Base\modules\System\Models\ConnectorLog;

class ConnectorLogListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        public Response $response
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ConnectorLogEvent $event): void
    {
        try {
            if (! Route::currentRouteAction()) {
                return;
            }

            [$controllerNamespace, $action] = explode('@', Route::currentRouteAction());

            $requestStartAt = app(Kernel::class)->requestStartedAt()->getPreciseTimestamp(3);

            $timeTaken = intval(microtime(true) * 1000 - $requestStartAt);

            $connectorLog = [
                'username' => $event->username ?: 'unknown',
                'path' => request()->path(),
                'method' => request()->method(),
                'user_agent' => request()->userAgent(),
                'ip' => request()->getClientIp(),
                'controller' => $controllerNamespace,
                'action' => $action,
                'time_taken' => $timeTaken,
                'status_code' => $this->response->getStatusCode(),
                'from' => $event->from,
                'creator_id' => $event->userId ?: 0,
                'created_at' => intval($requestStartAt / 1000),
                'updated_at' => time(),
            ];
            Redis::lPush(ConnectorLog::QUEUE_LOG_NAME, json_encode($connectorLog));
        } catch (\Exception $exception) {
            Log::error('接口日志记录异常：'.$exception->getMessage());
        }
    }
}
