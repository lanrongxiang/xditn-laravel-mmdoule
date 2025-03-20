<?php

// 这是一个 demo 控制器
// 可以删除
// 只做演示用

namespace Xditn\Base\modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\XditnController;

/**
 * @group 公共模块
 *
 * @subgroup 公共演示
 * @subgroupDescription MModule 后台公共演示
 */
class DemoController extends XditnController
{
    /**
     * 异常演示
     *
     * @param  Request  $request
     * @return mixed
     *
     * @throws \Exception
     */
    public function exception(Request $request)
    {
        throw new \Exception($request->get('message'));
    }

    /**
     * dd 打印演示
     *
     * @param  Request  $request
     * @return void
     */
    public function dd(Request $request)
    {
        dd_($request->get('message'));
    }
}
