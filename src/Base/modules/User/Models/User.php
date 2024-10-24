<?php

namespace Xditn\Modules\User\Models;

use Xditn\Base\XditnModel as Model;
use Xditn\Enums\Status;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Xditn\Modules\User\Models\Traits\UserRelations;
use Illuminate\Auth\Authenticatable;

/**
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $avatar
 * @property string $password
 * @property int $creator_id
 * @property int $status
 * @property string $login_ip
 * @property int $login_at
 * @property int $created_at
 * @property int $updated_at
 * @property string $remember_token
 */
class User extends Model implements AuthenticatableContract
{
    use Authenticatable;
    use HasApiTokens;
    use UserRelations;

    protected $fillable = [
        'id', 'username', 'email', 'avatar', 'password', 'remember_token', 'creator_id', 'status', 'department_id', 'login_ip', 'login_at', 'created_at', 'updated_at', 'deleted_at'
    ];

    protected array $defaultHidden = ['password', 'remember_token'];

    /**
     * @var array|string[]
     */
    public array $searchable = [
        'username' => 'like',
        'email' => 'like',
        'status' => '=',
    ];

    /**
     * @var string
     */
    protected $table = 'users';

    protected array $fields = ['id', 'username', 'email', 'avatar',  'creator_id', 'status', 'department_id', 'created_at'];

    /**
     * @var array|string[]
     */
    protected array $form = ['username', 'email', 'password', 'department_id'];

    /**
     * @var array|string[]
     */
    protected array $formRelations = ['roles', 'jobs'];

    /**
     * password
     *
     * @return Attribute
     */
    protected function password(): Attribute
    {
        return new Attribute(
            // get: fn($value) => '',
            set: fn ($value) => bcrypt($value),
        );
    }

    protected function DepartmentId(): Attribute
    {
        return new Attribute(
            get: fn($value) => $value ? : null,
            set: fn($value) => $value ? : 0
        );
    }

    /**
     * is super admin
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->{$this->primaryKey} == config('xditn.super_admin');
    }

    /**
     * update
     * @param $id
     * @param array $data
     * @return mixed
     */
    public function updateBy($id, array $data): mixed
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return parent::updateBy($id, $data);
    }

    public function isDisabled(): bool
    {

        return $this->status == Status::Disable->value;
    }
}
