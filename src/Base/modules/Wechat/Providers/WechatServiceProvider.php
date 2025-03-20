<?php

namespace Xditn\Base\modules\Wechat\Providers;

use Xditn\Providers\XditnModuleServiceProvider;

class WechatServiceProvider extends XditnModuleServiceProvider
{
    /**
     * route path
     */
    public function moduleName(): string
    {
        // TODO: Implement path() method.
        return 'wechat';
    }
}
