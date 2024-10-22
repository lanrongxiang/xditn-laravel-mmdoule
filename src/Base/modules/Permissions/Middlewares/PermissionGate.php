<?php

namespace Xditn\Modules\Permissions\Middlewares;

use Illuminate\Http\Request;
use Xditn\Modules\Permissions\Exceptions\PermissionForbidden;
use Xditn\Modules\User\Models\User;

class PermissionGate
{
    public function handle(Request $request, \Closure $next)
    {
        if ($request->isMethod('get')) {
            return $next($request);
        }

        /* @var User $user */
        $user = $request->user(getGuardName());

        if (! $user->can()) {
            throw new PermissionForbidden();
        }

        return $next($request);
    }
}
