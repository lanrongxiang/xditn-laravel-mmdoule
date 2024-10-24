<?php

declare(strict_types=1);

namespace Xditn\Modules\System\Http\Controllers;

use Xditn\Base\XditnController as Controller;
use Xditn\Modules\System\Models\DictionaryValues;
use Illuminate\Http\Request;

class DictionaryValuesController extends Controller
{
    public function __construct(
        protected readonly DictionaryValues $model
    )
    {
    }

    /**
     * @return mixed
     */
    public function index(): mixed
    {
        return $this->model->getList();
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        return $this->model->storeBy($request->all());
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function show($id)
    {
        return $this->model->firstBy($id);
    }

    /**
     * @param Request $request
     * @param         $id
     *
     * @return mixed
     */
    public function update($id, Request $request)
    {
        return $this->model->updateBy($id, $request->all());
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->model->deleteBy($id);
    }

    public function enable($id)
    {
        return $this->model->toggleBy($id);
    }
}
