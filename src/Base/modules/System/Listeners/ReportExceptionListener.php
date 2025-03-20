<?php

namespace Xditn\Base\modules\System\Listeners;

use Xditn\Base\modules\System\Models\Webhooks;
use Xditn\Base\modules\System\Support\Webhook;
use Xditn\Events\ReportException;

class ReportExceptionListener
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
     * Handle the event.
     *
     * @param  ReportException  $event
     * @return void
     */
    public function handle(ReportException $event): void
    {
        //
        $webhook = new Webhook(Webhooks::exceptions());

        $webhook->setValues([$event->exception->getMessage(), $event->exception->getFile()])->send();
    }
}
