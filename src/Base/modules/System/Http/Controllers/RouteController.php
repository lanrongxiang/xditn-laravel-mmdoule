<?php

namespace Xditn\Base\modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Xditn\Base\modules\System\Support\Routes;
use Xditn\Base\XditnController as Controller;

class RouteController extends Controller
{
    public function index(Routes $route, Request $request)
    {
        return $route->all($request->all());
    }

    public function cache()
    {
        return Artisan::call('route:cache');
    }
}
