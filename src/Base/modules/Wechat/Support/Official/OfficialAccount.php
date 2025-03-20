<?php

namespace Xditn\Base\modules\Wechat\Support\Official;

use Throwable;

class OfficialAccount extends Official
{
    /**
     * 公众号验证
     */
    public function serve(): mixed
    {
        try {
            return $this->app->getServer()->serve();
        } catch (Throwable $e) {
            return response();
        }
    }
}
