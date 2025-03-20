<?php

declare(strict_types=1);

namespace Xditn\Base\modules\System\Http\Controllers;

use Xditn\Base\modules\System\Models\SystemSmsCode;
use Xditn\Base\XditnController as Controller;

class SystemSmsCodeController extends Controller
{
    public function __construct(
        protected readonly SystemSmsCode $model
    ) {
    }

    public function index(): mixed
    {
        return $this->model->getList();
    }

    /**
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->model->deleteBy($id);
    }
}
