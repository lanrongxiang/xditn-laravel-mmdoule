<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Wechat\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Xditn\Base\modules\Wechat\Models\WechatNews;
use Xditn\Base\XditnController as Controller;

class WechatNewsController extends Controller
{
    public function __construct(
        protected readonly WechatNews $model
    ) {
    }

    public function index(): mixed
    {
        return $this->model->getList();
    }

    /**
     * @return mixed
     */
    public function store(Request $request)
    {
        return $this->model->storeBy($request->all());
    }

    /**
     * @return mixed
     */
    public function show($id)
    {
        if (Str::of($id)->explode(',')->count() == 1) {
            return $this->model->firstBy($id);
        }

        return $this->model->whereIn('id', Str::of($id)->explode(','))->get();
    }

    /**
     * @return mixed
     */
    public function update($id, Request $request)
    {
        return $this->model->updateBy($id, $request->all());
    }

    /**
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->model->deleteBy($id);
    }

    public function enable($id, Request $request)
    {
        return $this->model->toggleBy($id, $request->get('field'));
    }
}
