<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Domain\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\Domain\Models\Domains;
use Xditn\Base\XditnController as Controller;

class DomainsController extends Controller
{
    public function __construct(
        protected readonly Domains $model
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
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request)
    {
        return $this->model->storeBy($request->all());
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        return $this->model->firstBy($id);
    }

    /**
     * @param  Request  $request
     * @param $id
     * @return mixed
     */
    public function update($id, Request $request)
    {
        return $this->model->updateBy($id, $request->all());
    }

    /**
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->model->deleteBy($id);
    }
}
