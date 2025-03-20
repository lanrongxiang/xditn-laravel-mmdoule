<?php

namespace Xditn\Base\modules\Develop\Listeners;

use Xditn\Events\Module\Deleted;
use Xditn\MModule;

class DeletedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function handle(Deleted $event): void
    {
        MModule::deleteModulePath($event->module['path']);
    }
}
