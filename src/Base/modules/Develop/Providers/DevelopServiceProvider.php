<?php

namespace Xditn\Base\modules\Develop\Providers;

use Xditn\Base\modules\Develop\Listeners\CreatedListener;
use Xditn\Base\modules\Develop\Listeners\DeletedListener;
use Xditn\Events\Module\Created;
use Xditn\Events\Module\Deleted;
use Xditn\Providers\XditnModuleServiceProvider;

class DevelopServiceProvider extends XditnModuleServiceProvider
{
    protected array $events = [
        Created::class => CreatedListener::class,

        // Deleted::class => DeletedListener::class,
    ];

    /**
     * route path
     */
    public function moduleName(): string|array
    {
        // TODO: Implement path() method.
        return 'develop';
    }
}
