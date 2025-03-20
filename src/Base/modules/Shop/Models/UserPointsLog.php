<?php

namespace Xditn\Base\modules\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Xditn\Traits\DB\BaseOperate;
use Xditn\Traits\DB\ScopeTrait;
use Xditn\Traits\DB\TransTraits;
use Xditn\Traits\DB\WithAttributes;

class UserPointsLog extends Model
{
    use BaseOperate;
    use ScopeTrait;
    use TransTraits;
    use WithAttributes;

    protected $table = 'shop_user_points_log';

    protected $fillable = [
        'user_id',
        'point_num',
        'type',
        'description',
        'remark',
    ];

    protected $dateFormat = 'U';
}
