<?php

namespace Modules\Develop\Providers;

use Modules\Develop\Listeners\CreatedListener;
use Modules\Develop\Listeners\DeletedListener;
use Xditn\Events\Module\Created;
use Xditn\Events\Module\Deleted;
use Xditn\Providers\XditnModuleServiceProvider;

class DevelopServiceProvider extends XditnModuleServiceProvider
{
    protected array $events = [
        Created::class => CreatedListener::class,

        Deleted::class => DeletedListener::class
    ];

    /**
     * route path
     *
     * @return string|array
     */
    public function moduleName(): string|array
    {
        // TODO: Implement path() method.
        return 'develop';
    }
}