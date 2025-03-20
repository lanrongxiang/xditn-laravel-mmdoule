<?php

namespace Xditn\Base\modules\System\Support\Traits;

use Xditn\Base\modules\System\Models\AsyncTask;

/**
 * @method array getParams()
 */
trait AsyncTaskDispatch
{
    public function push(): mixed
    {
        return app(AsyncTask::class)
            ->storeBy([
                'task' => get_called_class(),
                'params' => count($this->getParams()) ? json_encode($this->getParams()) : '',
            ]);
    }
}
