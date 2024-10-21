<?php

namespace Modules\User\Providers;

use Modules\User\Events\Login;
use Modules\User\Listeners\Login as LoginListener;
use Modules\User\Middlewares\OperatingMiddleware;
use Xditn\Providers\XditnModuleServiceProvider;

class UserServiceProvider extends XditnModuleServiceProvider
{
    protected array $events = [
        Login::class => LoginListener::class
    ];

    /**
     * route path
     *
     * @return string|array
     */
    public function moduleName(): string|array
    {
        // TODO: Implement path() method.
        return 'user';
    }

    /**
     * @return string[]
     */
    protected function middlewares(): array
    {
        return [OperatingMiddleware::class];
    }
}
