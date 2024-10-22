<?php

namespace Xditn\Modules\Common\Providers;

use Xditn\Modules\User\Events\Login;
use Xditn\Modules\User\Listeners\Login as LoginListener;
use Xditn\Modules\User\Middlewares\OperatingMiddleware;
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
