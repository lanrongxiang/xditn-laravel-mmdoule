<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Openapi\Http\Controllers;

use Xditn\Base\modules\Openapi\Models\OpenapiRequestLog;
use Xditn\Base\XditnController as Controller;

class OpenapiRequestLogController extends Controller
{
    public function __construct(
        protected readonly OpenapiRequestLog $model
    ) {
    }

    /**
     * @return mixed
     */
    public function index(): mixed
    {
        return $this->model->getList();
    }

    /**
     * @param $id
     * @return bool|null
     */
    public function destroy($id): ?bool
    {
        return $this->model->deleteBy($id);
    }
}
