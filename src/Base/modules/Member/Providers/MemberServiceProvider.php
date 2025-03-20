<?php

namespace Xditn\Base\modules\Member\Providers;

use Xditn\Providers\XditnModuleServiceProvider;

class MemberServiceProvider extends XditnModuleServiceProvider
{
    /**
     * route path
     */
    public function moduleName(): string
    {
        // TODO: Implement path() method.
        return 'Member';
    }
}
