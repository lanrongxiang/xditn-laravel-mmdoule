<?php

namespace Xditn\Base\modules\User\Models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class LogLogin extends Model
{
    protected $table = 'log_login';

    public $timestamps = false;

    protected $fillable = [
        'id', 'account', 'login_ip', 'token_id', 'location', 'browser', 'platform', 'login_at', 'status',
    ];

    protected $casts = [
        'login_at' => 'datetime:Y-m-d H:i',
    ];

    /**
     * @param  ?string  $email
     * @return LengthAwarePaginator
     */
    public function getUserLogBy(?string $email): LengthAwarePaginator
    {
        return static::when($email, function ($query) use ($email) {
            $query->where('account', $email);
        })
            ->orderByDesc('id')
            ->paginate(request()->get('limit', 10));
    }
}
