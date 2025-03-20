<?php

namespace Xditn\Base\modules\Permissions\Middlewares;

use Illuminate\Http\Request;
use Xditn\Base\modules\Permissions\Exceptions\PermissionForbidden;
use Xditn\Base\modules\User\Models\User;
use Xditn\Exceptions\FailedException;

class PermissionGate
{
    public function handle(Request $request, \Closure $next)
    {
        if ($request->isMethod('get')) {
            return $next($request);
        }

        if (config('app.env') == 'demo') {
            throw new FailedException('演示环境禁止操作');
        }

        /* @var User $user */
        $user = $request->user(getGuardName());

        if (! $user->can()) {
            throw new PermissionForbidden();
        }

        return $next($request);
    }
}
