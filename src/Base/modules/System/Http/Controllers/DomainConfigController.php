<?php

namespace Xditn\Base\modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\System\Models\SystemConfig as Config;
use Xditn\Base\modules\System\Support\Configure;
use Xditn\Base\XditnController as Controller;

class DomainConfigController extends Controller
{
    public function index()
    {
        return \config('domain');
    }

    public function store(Request $request, Config $configModel)
    {
        $type = $request->get('type');

        return $configModel->storeBy(Configure::parse("domain.$type", $request->except('type')));
    }
}
