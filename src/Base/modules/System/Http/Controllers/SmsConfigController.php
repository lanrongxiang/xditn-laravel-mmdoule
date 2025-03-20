<?php

namespace Xditn\Base\modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\System\Models\SystemConfig as Config;
use Xditn\Base\modules\System\Support\Configure;
use Xditn\Base\XditnController as Controller;

class SmsConfigController extends Controller
{
    public function store(Request $request, Config $configModel)
    {
        if ($default = $request->get('default')) {
            return $configModel->storeBy([
                'sms.default' => $default,
            ]);
        }

        $driver = $request->get('channel');

        return $configModel->storeBy(Configure::parse("sms.$driver", $request->except('channel')));
    }

    public function show($driver)
    {
        return \config('sms.'.$driver);
    }
}
