<?php

namespace Xditn\Base\modules\Common\Http\Controllers;

use Exception;

/**
 * @group 公共模块
 *
 * @subgroup 功能开启
 * @subgroupDescription MModule 是否开启
 */
class EnableController
{
    /**
     * 登录微信/手机功能是否开启
     *
     * @responseField wechat bool 微信登录功能是否开启
     * @responseField mobile bool 手机登录功能是否开启
     *
     * @throws Exception
     */
    public function login(): array
    {
        return [
            'wechat' => (bool) config('wechat.pc.app_id', false),
            'mobile' => (bool) config('sms.default', false),
        ];
    }
}
