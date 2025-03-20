<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Member\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Sanctum\HasApiTokens;
use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $email
 * @property $mobile
 * @property $password
 * @property $avatar
 * @property $username
 * @property $address_id
 * @property $from
 * @property $status
 * @property $token
 * @property $last_login_at
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class Members extends Model implements AuthenticatableContract
{
    use Authenticatable, HasApiTokens;

    protected $table = 'members';

    protected bool $autoNull2EmptyString = false;

    protected $fillable = [
        'id',
        'email',
        'mobile',
        'password',
        'avatar',
        'username',
        'token',
        'address_id',
        'from',
        'status',
        'last_login_at',
        'creator_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected array $fields = ['id', 'email', 'mobile', 'password', 'avatar', 'username', 'from', 'status', 'last_login_at', 'created_at'];

    protected array $form = ['email', 'mobile', 'password', 'avatar', 'username', 'from'];

    public array $searchable = [
        'username' => 'like',
        'email' => 'like',
        'mobile' => 'like',
    ];

    /**
     * 加密密码
     */
    protected function password(): Attribute
    {
        return new Attribute(
            set: fn ($value) => bcrypt($value),
        );
    }
}
