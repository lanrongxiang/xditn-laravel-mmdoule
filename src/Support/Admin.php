<?php

namespace Xditn\Support;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Modules\User\Models\User as User;
use Xditn\Exceptions\TokenExpiredException;

class Admin
{
    protected ?User $user = null;

    /**
     * admin 后台用户认证
     *
     * @return User
     *
     * @throws AuthenticationException
     */
    public function auth(): User
    {
        try {
            [$tokenId, $token] = $this->parseBearerToken();
            $userCacheKey = $this->getUserCacheKey($tokenId);
            // 这里返回可能是 bool ，也可能是 personal token 模型
            if (! $personalToken = $this->verifyPersonalToken($tokenId, $token, $userCacheKey)) {
                throw new AuthenticationException();
            }
            // 如果缓存中没有缓存用户，就从数据库中获取并缓存
            $user = Cache::get($userCacheKey);
            if (! $user && $personalToken instanceof PersonalAccessToken) {
                $user = $personalToken->tokenable;
                Cache::put($userCacheKey, $user, now()->addHours(2));
            }
            if (! $user instanceof User) {
                throw new AuthenticationException();
            }
            // 更新最近使用
            $this->updatePersonalTokenLastUsed($tokenId);
            // 单例
            $this->user = $user;

            return $user;
        } catch (TokenExpiredException $e) {
            $this->logout();
            throw new AuthenticationException();
        }
    }

    /**
     * @return array
     *
     * @throws AuthenticationException
     */
    protected function parseBearerToken(): array
    {
        $bearerToken = request()->bearerToken();
        if (! is_null($bearerToken) && ! str_contains($bearerToken, '|')) {
            throw new AuthenticationException();
        }

        return explode('|', $bearerToken, 2);
    }

    /**
     * 用户信息 KEY
     */
    protected function getUserCacheKey($tokenId): string
    {
        return 'user_personal_token_'.$tokenId;
    }

    /**
     * @param    $tokenId
     * @param  string  $token
     * @param  string  $userCacheKey
     * @return mixed
     *
     * @throws AuthenticationException
     */
    protected function verifyPersonalToken($tokenId, string $token, string $userCacheKey): mixed
    {
        $personalTokenKey = $this->getPersonalTokenKey($tokenId);
        if ($accessToken = $this->getPersonalToken($personalTokenKey)) {
            // 这里还需要校验保存的 token 和 头信息 token，防止串改
            return hash_equals($accessToken, hash('sha256', $token));
        }
        // 如果缓存的 token 不存在，那么需要先通过数据库校验
        $personalToken = $this->validPersonalToken($tokenId);
        if (! hash_equals($personalToken->token, hash('sha256', $token))) {
            throw new AuthenticationException();
        }
        // 判断 user 缓存，如果 access token 失效，那么对应的缓存用户就应该删除
        Cache::has($userCacheKey) && Cache::delete($userCacheKey);
        // 保存这个已经校验的 token 到内存，并且设置到过期时间
        $this->setPersonalToken($personalTokenKey, $personalToken);
        // 保存 token id
        $this->tokenIds($tokenId);

        return $personalToken;
    }

    /**
     * 用户令牌 KEY
     *
     * @param $tokenId
     * @return string
     */
    protected function getPersonalTokenKey($tokenId): string
    {
        return 'personal_token_'.$tokenId;
    }

    /**
     * 获取个人令牌
     *
     * @param $tokenKey
     * @return mixed
     */
    protected function getPersonalToken($tokenKey): mixed
    {
        return Cache::get($tokenKey);
    }

    /**
     * @param $tokenId
     * @return mixed
     *
     * @throws AuthenticationException
     */
    protected function validPersonalToken($tokenId): mixed
    {
        // 获取 person token 模型
        $personalToken = Sanctum::$personalAccessTokenModel::find($tokenId);
        if (! $personalToken) {
            throw new AuthenticationException();
        }
        if ($this->isPersonalTokenExpired($personalToken)) {
            throw new TokenExpiredException();
        }

        return $personalToken;
    }

    /**
     * 判断令牌是否过期
     *
     * @param $personalToken
     * @return bool
     */
    protected function isPersonalTokenExpired($personalToken): bool
    {
        if (! $personalToken->expires_at?->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * 设置个人 token
     *
     * @param $tokenKey
     * @param $personalToken
     * @return true
     */
    protected function setPersonalToken($tokenKey, $personalToken): true
    {
        if (! $personalToken->expires_at) {
            Cache::forever($tokenKey, $personalToken->token);
        } else {
            Cache::put($tokenKey, $personalToken->token, $personalToken->expires_at);
        }

        return true;
    }

    /**
     * get token ids
     *
     * @param $tokenId
     * @return void
     */
    protected function tokenIds($tokenId): void
    {
        $tokenIds = Cache::get($this->getTokenIdsKey(), []);
        $tokenIds[] = $tokenId;
        Cache::forever($this->getTokenIdsKey(), array_unique($tokenIds));
    }

    /**
     * token 集合 key
     *
     * @return string
     */
    protected function getTokenIdsKey(): string
    {
        return 'personal_token_ids';
    }

    /**
     * 更新个人 token 最近使用使用
     *
     * @param $tokenId
     * @return void
     */
    protected function updatePersonalTokenLastUsed($tokenId): void
    {
        $personalTokenLastUsedKey = 'personal_token_last_used_'.$tokenId;
        // 标记十分钟更新一次，不需要每次请求都去更新
        try {
            if (! Cache::has($personalTokenLastUsedKey)) {
                app(Sanctum::$personalAccessTokenModel)->where('id', $tokenId)->update(['last_used_at' => now()]);
                Cache::put($personalTokenLastUsedKey, true, now()->addMinutes(10));
            }
        } catch (Exception $e) {
        }
    }

    /**
     * @return true
     *
     * @throws AuthenticationException
     */
    public function logout(): true
    {
        $this->clearUserPersonalToken();
        // 删除 token
        [$tokenId, $token] = $this->parseBearerToken();
        app(Sanctum::$personalAccessTokenModel)->where('id', $tokenId)->delete();
        //        $this->currentLoginUser()?->tokens()->where('id', $tokenId)->delete();
        return true;
    }

    /**
     * 清理个人令牌
     *
     * @param  null  $tokenId
     * @return void
     *
     * @throws AuthenticationException
     */
    public function clearUserPersonalToken($tokenId = null): void
    {
        if (! $tokenId) {
            [$tokenId, $token] = $this->parseBearerToken();
        }
        Cache::delete($this->getUserCacheKey($tokenId));
        Cache::delete($this->getPersonalTokenKey($tokenId));
    }

    /**
     * @return int|null
     */
    public function id(): ?int
    {
        return $this->currentLoginUser()?->id;
    }

    /**
     * 获取当前登录用户
     *
     * @return User|null
     */
    public function currentLoginUser(): ?User
    {
        return $this->user;
    }

    /**
     * @return void
     *
     * @throws AuthenticationException
     */
    public function clearAllCachedUsers(): void
    {
        foreach (Cache::get($this->getTokenIdsKey()) as $tokenId) {
            $this->clearUserPersonalToken($tokenId);
        }
        // 最后清除 token id 集合缓存
        Cache::delete($this->getTokenIdsKey());
    }
}
