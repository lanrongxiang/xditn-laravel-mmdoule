<?php

namespace Xditn\Base\modules\Wechat\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\Wechat\Support\Official\OfficialMenu;
use Xditn\Base\XditnController;

class OfficialMenuController extends XditnController
{
    public function __construct(protected OfficialMenu $menu)
    {
    }

    public function index()
    {

    }

    public function store(Request $request)
    {
        $this->menu->create($request->all());
    }
}
