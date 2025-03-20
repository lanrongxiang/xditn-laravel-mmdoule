<?php

namespace Xditn\Base\modules\User\Models;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Xditn\Base\modules\System\Events\ConnectorLogEvent;
use Xditn\Base\modules\System\Models\ConnectorLog;
use Xditn\Facade\Admin;
use Xditn\MModule;
use Xditn\Traits\DB\BaseOperate;
use Xditn\Traits\DB\ScopeTrait;
use Xditn\Traits\DB\TransTraits;
use Xditn\Traits\DB\WithAttributes;

class LogOperate extends Model
{
    use BaseOperate;
    use ScopeTrait;
    use TransTraits;
    use WithAttributes;

    protected $table = 'log_operate';

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
    ];

    protected $fillable = [
        'id',
        'module',
        'action',
        'params',
        'ip',
        'http_method',
        'http_code',
        'start_at',
        'time_taken',
        'creator_id',
        'created_at',
    ];

    public function log(Request $request, Response $response): void
    {
        $user = Admin::currentLoginUser();

        // 记录接口日志
        Event::dispatch(new ConnectorLogEvent(
            $user?->username,
            $user?->id,
            ConnectorLog::FROM_DASHBOARD
        ));

        $userModel = getAuthUserModel();
        if (! $user instanceof $userModel) {
            return;
        }

        if (! Route::currentRouteAction()) {
            return;
        }

        [$module, $controller, $action] = MModule::parseFromRouteAction();

        $requestStartAt = app(Kernel::class)->requestStartedAt()->getPreciseTimestamp(3);
        $params = $request->all();

        // 如果参数过长则不记录
        if (! empty($params)) {
            if (strlen(\json_encode($params, JSON_UNESCAPED_UNICODE)) > 5000) {
                $params = [];
            }
        }

        $timeTaken = intval(microtime(true) * 1000 - $requestStartAt);
        $this->storeBy([
            'module' => $module,
            'action' => $controller.'@'.$action,
            'creator_id' => $user->id,
            'http_method' => $request->method(),
            'http_code' => $response->getStatusCode(),
            'start_at' => intval($requestStartAt / 1000),
            'time_taken' => $timeTaken,
            'ip' => $request->ip(),
            'params' => \json_encode($params, JSON_UNESCAPED_UNICODE),
            'created_at' => time(),
        ]);
    }

    protected function timeTaken(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value > 1000 ? intval($value / 1000).'s' : $value.'ms',
        );
    }
}
