<?php

namespace Modules\Common\Providers;

use Modules\User\Events\Login;
use Modules\User\Listeners\Login as LoginListener;
use Modules\User\Middlewares\OperatingMiddleware;
use Xditn\Providers\XditnServiceProvider;

class CommonServiceProvider extends XditnServiceProvider
{
    /**
     * route path
     *
     * @return string|array
     */
    public function moduleName(): string|array
    {
        // TODO: Implement path() method.
        return 'common';
    }
}
