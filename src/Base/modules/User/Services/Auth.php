<?php

namespace Xditn\Base\modules\User\Services;

use Illuminate\Support\Facades\Event;
use Xditn\Base\modules\User\Events\Login;
use Xditn\Base\modules\User\Services\Login\Factory;
use Xditn\Exceptions\FailedException;

class Auth
{
    public function attempt(array $params): array
    {
        try {
            $auth = Factory::make($params);

            $user = $auth->auth($params);

            $token = $user->createToken('token')->plainTextToken;
            Event::dispatch(new Login($user, $token));

            return compact('token');
        } catch (\Exception|\Throwable $e) {
            // 登录失败日志
            Event::dispatch(new Login(null));
            throw new FailedException($e->getMessage());
        }
    }
}
