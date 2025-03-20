<?php

namespace Xditn\Base\modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\System\Support\Sms\SmsCode;
use Xditn\Base\modules\User\Http\Requests\SmsCodeRequest;
use Xditn\Base\modules\User\Services\Auth as AuthService;
use Xditn\Base\XditnController as Controller;
use Xditn\Exceptions\FailedException;
use Xditn\Facade\Admin;

/**
 * @group 用户模块
 *
 * MModule 后台用户认证
 *
 * @subgroup 用户认证
 * @subgroupDescription MModule 后台用户认证
 */
class AuthController extends Controller
{
    /**
     * 登录
     *
     * @bodyParam account string required 账号
     * @bodyParam password string required 密码
     * @bodyParam remember boolean 记住我
     * @bodyParam mobile string 手机号
     * @bodyParam sms_code string 短信验证码
     * @bodyParam wx_code string 微信code
     *
     * @responseField token string 用户授权 token
     * @unauthenticated
     *
     * @param  Request  $request
     * @param  AuthService  $auth
     * @return array
     */
    public function login(Request $request, AuthService $auth): array
    {
        return $auth->attempt($request->all());
    }

    /**
     * 退出
     */
    public function logout(): array
    {
       Admin::logout();

        return [];
    }

    /**
     * 登录短信验证码
     *
     * @bodyParam mobile string 手机号
     *
     * @throws \Throwable
     */
    public function loginSmsCode(SmsCodeRequest $request, SmsCode $smsCode): array
    {
        return [$smsCode->login($request->get('mobile'))];
    }

    /**
     * 微信登录配置
     *
     * @return array|\Illuminate\Config\Repository|\Illuminate\Foundation\Application|mixed
     */
    public function wechat()
    {
        $wechatPcConfig = config('wechat.pc');
        if (empty($wechatPcConfig)) {
            throw new FailedException('请先通过密码登录方式登录，在系统管理中配置微信相关配置');
        }

        unset($wechatPcConfig['app_secret']);
        $wechatPcConfig['callback'] = urlencode($wechatPcConfig['callback']);

        return $wechatPcConfig;
    }
}
