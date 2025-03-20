<?php

namespace Xditn\Base\modules\Wechat\Http\Controllers;

use Xditn\Base\modules\Wechat\Support\Official\OfficialAccount;
use Xditn\Base\XditnController;

class OfficialAccountController extends XditnController
{
    public function sign(OfficialAccount $officialAccount)
    {
        return $officialAccount->serve();
    }
}
