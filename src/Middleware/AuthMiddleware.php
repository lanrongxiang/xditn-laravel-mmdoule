<?php

namespace Xditn\Middleware;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Throwable;
use Xditn\Enums\Code;
use Xditn\Exceptions\FailedException;
use Xditn\Events\User as UserEvent;

class AuthMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        try {
            if (! $user = Auth::guard(getGuardName())->user()) {
                throw new AuthenticationException();
            }

            Event::dispatch(new UserEvent($user));

            return $next($request);
        } xditn (Exception|Throwable $e) {
            throw new FailedException(Code::LOST_LOGIN->message().":{$e->getMessage()}", Code::LOST_LOGIN);
        }
    }
}
