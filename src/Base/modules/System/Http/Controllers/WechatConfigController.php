<?php

declare(strict_types=1);

namespace Xditn\Base\modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\System\Models\SystemConfig;
use Xditn\Base\modules\System\Support\Configure;
use Xditn\Base\XditnController as Controller;

class WechatConfigController extends Controller
{
    public function __construct(
        protected readonly SystemConfig $model
    ) {
    }

    public function store(Request $request)
    {
        $driver = $request->get('driver');
        $config = Configure::parse("wechat.$driver", $request->except('driver'));

        return $this->model->storeBy($config);
    }

    public function show($driver = null)
    {
        return config("wechat.$driver");
    }
}
