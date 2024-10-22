<?php

namespace Xditn\Modules\Develop\Listeners;

use Xditn\Modules\Develop\Support\Generate\Module;
use Xditn\Events\Module\Created;

class CreatedListener
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
     * @param Created $event
     * @return void
     */
    public function handle(Created $event): void
    {
        $module = $event->module;

        (new Module(
            $module['path'],
            $module['dirs']['controllers'],
            $module['dirs']['models'],
            $module['dirs']['requests'],
            $module['dirs']['database']
        )
        )->create();
    }
}
