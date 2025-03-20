<?php

namespace Xditn\Base\modules\User\Providers;

use Xditn\Base\modules\User\Console\PasswordCommand;
use Xditn\Base\modules\User\Events\Login;
use Xditn\Base\modules\User\Listeners\Login as LoginListener;
use Xditn\Base\modules\User\Middlewares\OperatingMiddleware;
use Xditn\Providers\XditnModuleServiceProvider;

class UserServiceProvider extends XditnModuleServiceProvider
{
    protected array $events = [
        Login::class => LoginListener::class,
    ];

    protected array $commands = [
        PasswordCommand::class,
    ];

    /**
     * route path
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
