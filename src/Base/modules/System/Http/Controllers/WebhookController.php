<?php

declare(strict_types=1);

namespace Xditn\Base\modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\System\Models\Webhooks;
use Xditn\Base\modules\System\Support\Webhook;
use Xditn\Base\XditnController as Controller;

class WebhookController extends Controller
{
    public function __construct(
        protected readonly Webhooks $model
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

    public function enable($id)
    {
        return $this->model->toggleBy($id);
    }

    /**
     * @param $id
     * @return bool
     */
    public function test($id)
    {
        $webhook = new Webhook($this->model->firstBy($id));

        return $webhook->send('Hello World, I am a robot!');
    }
}
