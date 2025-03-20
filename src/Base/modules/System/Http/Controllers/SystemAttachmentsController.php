<?php

declare(strict_types=1);

namespace Xditn\Base\modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\System\Models\SystemAttachments;
use Xditn\Base\XditnController as Controller;

class SystemAttachmentsController extends Controller
{
    public function __construct(
        protected readonly SystemAttachments $model
    ) {
    }

    public function index(): mixed
    {
        return $this->model->getList();
    }

    public function store(Request $request): mixed
    {
        return $this->model->storeBy($request->all());
    }

    /**
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->model->deletesBy($id);
    }
}
