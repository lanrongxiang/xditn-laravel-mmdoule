<?php

namespace Xditn\Base\modules\User\Export;

use Xditn\Base\modules\System\Support\Traits\AsyncTaskDispatch;
use Xditn\Contracts\AsyncTaskInterface;
use Xditn\Support\Excel\Export;

class User extends Export implements AsyncTaskInterface
{
    use AsyncTaskDispatch;

    protected array $header = [
        'id', '昵称', '邮箱', '创建时间',
    ];

    public function array(): array
    {
        // TODO: Implement array() method.
        return \Xditn\Base\modules\User\Models\User::query()
            ->select('id', 'username', 'email', 'created_at')
            ->without('roles')
            ->get([
                'id', 'username', 'email', 'created_at',
            ])->toArray();
    }
}
