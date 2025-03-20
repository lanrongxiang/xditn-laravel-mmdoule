<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Openapi\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $username
 * @property $mobile
 * @property $company
 * @property $description
 * @property $qps
 * @property $app_key
 * @property $app_secret
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class Users extends Model
{
    protected $table = 'openapi_users';

    protected $fillable = ['id', 'username', 'mobile', 'password', 'qps', 'company', 'description', 'app_key', 'app_secret', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    protected array $fields = ['id', 'username', 'mobile',  'company', 'qps', 'description', 'app_key', 'app_secret', 'created_at', 'updated_at'];

    protected array $form = ['username', 'mobile', 'company', 'password', 'qps', 'description'];

    public array $searchable = [
        'username' => 'like',
        'mobile' => 'like',
    ];

    public $hidden = [
        'password',
    ];

    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        static::saving(function ($model) {
            if (! $model->primary) {
                $model->app_key = Str::random(20);
                $model->app_secret = Str::password(40);
            }
        });
    }

    /**
     * 密码
     */
    public function password(): Attribute
    {
        return new Attribute(
            set: fn ($value) => $value ? bcrypt($value) : '',
        );
    }

    /**
     * 重新生成密钥
     */
    public function regenerate($id): int
    {
        return static::query()
            ->where('id', $id)
            ->update([
                'app_key' => Str::random(20),
                'app_secret' => Str::password(40),
            ]);
    }
}
