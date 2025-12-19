<?php

namespace Xditn\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Xditn\Enums\Code;
use Xditn\Events\User as UserEvent;
use Xditn\Exceptions\FailedException;
use Xditn\Exceptions\TokenExpiredException;
use Xditn\Facade\Admin;

class AuthMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        try {
            $user = Admin::auth();
        } catch (AuthenticationException $e) {
            throw new FailedException('身份认证过期或失败'.$e->getMessage(), Code::LOST_LOGIN);
        } catch (TokenExpiredException $e) {
            throw new FailedException('Token 已过期', Code::LOST_LOGIN);
        }
        Event::dispatch(new UserEvent($user));

        return $next($request);
    }
}
