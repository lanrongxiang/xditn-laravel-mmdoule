<?php

namespace Xditn\Base\modules\Domain\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Xditn\Base\modules\Domain\Models\Domains;
use Xditn\Base\modules\Domain\Support\Request\Request as DomainRequest;
use Xditn\Base\XditnController as Controller;

class DomainRecordsController extends Controller
{
    protected DomainRequest $model;

    protected string $domain;

    public function __construct(Request $request)
    {
        $domainId = trim($request->get('id'), '/');
        $domainModel = Domains::find($domainId);
        if ($domainModel) {
            $this->domain = $domainModel->name;
            $this->model = $domainModel->api();
        }
    }

    public function index(Request $request): mixed
    {
        [$records, $total] = $this->model->getList($this->domain, $request->get('page') - 1, $request->get('limit'));

        return new LengthAwarePaginator($records, $total, $request->get('limit'), $request->get('page'));
    }

    public function store(Request $request): mixed
    {
        return $this->model->store($request->except('id'));
    }

    /**
     * @return mixed
     */
    public function show($id)
    {
        return $this->model->show($id, $this->domain);
    }

    /**
     * @return mixed
     */
    public function update($id, Request $request)
    {
        return $this->model->update($id, $request->all());
    }

    /**
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->model->destroy($id, $this->domain);
    }
}
