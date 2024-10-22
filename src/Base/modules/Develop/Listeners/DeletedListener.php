<?php

namespace Xditn\Modules\Develop\Listeners;


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

    /**
     *
     *
     * @param Deleted $event
     * @return void
     */
    public function handle(Deleted $event): void
    {
        MModule::deleteModulePath($event->module['path']);
    }
}
