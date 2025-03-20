<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\Shop\Services\ProductSpecTmpService;
use Xditn\Base\XditnController as Controller;

class ProductSpecTmpController extends Controller
{
    public function __construct(
        protected readonly ProductSpecTmpService $service
    ) {
    }

    public function index(): mixed
    {
        return $this->service->getList();
    }

    /**
     * @return mixed
     */
    public function store(Request $request)
    {
        return $this->service->store($request->all());
    }

    /**
     * @return mixed
     */
    public function show($id)
    {
        return $this->service->first($id);
    }

    /**
     * @return mixed
     */
    public function update($id, Request $request)
    {
        return $this->service->update($id, $request->all());
    }

    /**
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->service->destroy($id);
    }
}
