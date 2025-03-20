<?php

namespace Xditn\Base\modules\User\Services\Login;

use Xditn\Base\modules\User\Models\User;

interface LoginInterface
{
    public function auth(array $params): ?User;
}
