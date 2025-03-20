<?php

namespace Xditn\Base\modules\User\Services\Login;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Xditn\Base\modules\User\Models\User;
use Xditn\Exceptions\FailedException;

class Password implements LoginInterface
{
    /**
     * @param  array{account: string, password: string}  $params
     * @return ?User
     */
    public function auth(array $params): ?User
    {
        if (! $leftAttempts = $this->leftAttempts()) {
            throw new FailedException('尝试次数已达上限，请稍后再试');
        }

        /* @var User $user */
        $user = User::query()->where($this->getLoginField($params['account']), $params['account'])->first();
        // 登录成功
        if ($user && Hash::check($params['password'], $user->password)) {
            $this->resetAttempts();

            return $user;
        }

        throw new FailedException('登录失败！请检查邮箱或者密码, 剩余尝试登录次数：'.$leftAttempts);
    }

    /**
     * @param $password
     * @return array|false|string
     */
    protected function parsePassword($password): array|false|string
    {
        return str_replace(Request::header('Request-Auth'), '', base64_decode($password));
    }

    protected function getLoginField(string $account): string
    {
        if (filter_var($account, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        if (preg_match('/^1\d{10}$/', $account)) {
            return 'mobile';
        }

        return 'username';
    }

    protected function limitKey(): string
    {
        return 'admin:login:'.Request::host().':'.Request::ip();
    }

    protected function maxAttempts($maxLimit = 5): int
    {
        return $maxLimit;
    }

    protected function leftAttempts()
    {
        $leftAttempts = $this->maxAttempts() - $this->hitAttempts();

        if ($leftAttempts < 0) {
            return false;
        }

        $this->incAttempts();

        return $leftAttempts;
    }

    protected function hitAttempts()
    {
        return Cache::remember($this->limitKey(), $this->limitTtl(), function () {
            // 默认一次
            return 0;
        });
    }

    public function incAttempts(): void
    {
        Cache::increment($this->limitKey());
    }

    protected function limitTtl(int $ttl = 30): int
    {
        return $ttl;
    }

    protected function resetAttempts(): void
    {
        Cache::forget($this->limitKey());
    }
}
