<?php

namespace Xditn\Base\modules\Openapi\Models;

use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $user_id
 * @property $balance
 * @property $created_at
 * @property $updated_at
 */
class UserBalance extends Model
{
    protected $table = 'openapi_user_balance';

    protected $fillable = [
        'id', 'user_id', 'balance', 'created_at', 'updated_at', 'deleted_at',
    ];
}
