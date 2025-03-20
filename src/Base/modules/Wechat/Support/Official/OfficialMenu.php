<?php

namespace Xditn\Base\modules\Wechat\Support\Official;

class OfficialMenu extends OfficialAccount
{
    public function create(array $data)
    {
        return $this->post('menu/create', $data);
    }
}
