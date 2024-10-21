<?php

namespace Xditn\Base;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Xditn\Exceptions\FailedException;

abstract class XditnController extends Controller
{
    /**
     * 获取当前登录用户
     *
     * @param  string|null  $guard 认证守卫名称
     * @param  string|null  $field 需要获取的用户字段
     * @return mixed
     *
     * @throws FailedException 当用户未登录时抛出异常
     */
    protected function getLoginUser(string|null $guard = null, string|null $field = null): mixed
    {
        // 获取当前守卫的用户
        $user = Auth::guard($guard ?: getGuardName())->user();

        // 如果用户未登录，抛出异常
        if (! $user) {
            throw new FailedException('登录失效, 请重新登录', Code::LOST_LOGIN);
        }

        // 如果指定了字段，返回该字段的值，否则返回整个用户对象
        return $field ? $user->getAttribute($field) : $user;
    }

    /**
     * 获取当前登录用户的ID
     *
     * @param  string|null  $guard 认证守卫名称
     * @return mixed
     */
    protected function getLoginUserId(string|null $guard = null): mixed
    {
        // 调用 getLoginUser 方法获取用户的ID字段
        return $this->getLoginUser($guard, 'id');
    }
}
