<?php

namespace Xditn\Base\modules\Pay\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\System\Models\SystemConfig;
use Xditn\Base\modules\System\Support\Configure;
use Xditn\Base\XditnController as Controller;

class ConfigController extends Controller
{
    public function show($driver)
    {
        return config("pay.{$driver}");
    }

    public function store(Request $request, SystemConfig $config)
    {
        return $config->storeBy(Configure::parse('pay', $request->all()));
    }
}
