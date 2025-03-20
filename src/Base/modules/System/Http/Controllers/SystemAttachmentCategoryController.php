<?php

declare(strict_types=1);

namespace Xditn\Base\modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\System\Models\SystemAttachmentCategory;
use Xditn\Base\modules\System\Models\SystemAttachments;
use Xditn\Base\XditnController as Controller;

class SystemAttachmentCategoryController extends Controller
{
    public function __construct(
        protected readonly SystemAttachmentCategory $model
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

    public function update($id, Request $request): mixed
    {
        return $this->model->updateBy($id, $request->all());
    }

    /**
     * @return mixed
     */
    public function destroy($id, SystemAttachments $systemAttachments)
    {
        if ($this->model->deleteBy($id)) {
            // 更新附件所属分类为 0
            $systemAttachments->where('category_id', $id)->update([
                'category_id' => 0,
            ]);
        }

        return [];
    }
}
