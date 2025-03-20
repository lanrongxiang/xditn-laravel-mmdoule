<?php

namespace Xditn\Base\modules\Shop\Models\Pivots;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserHasCoupons extends Pivot
{
    protected $table = 'shop_user_has_coupons';

    protected $dateFormat = 'U';

    public function getList(array $params)
    {
        return self::query()
            ->join('shop_coupons', 'shop_coupons.id', '=', 'shop_user_has_coupons.coupon_id')
            ->join('members', 'members.id', '=', 'shop_user_has_coupons.user_id')
            ->select([
                'shop_user_has_coupons.created_at',
                'shop_user_has_coupons.status',
                'shop_user_has_coupons.id',
                'shop_coupons.title',
                'shop_coupons.type',
                'shop_coupons.reduce_price',
                'shop_coupons.discount',
                'shop_coupons.min_price',
                'shop_coupons.expire_type',
                'shop_coupons.validaty',
                'shop_coupons.start_at',
                'shop_coupons.end_at',
                'members.username',
            ])
            ->when($params['title'] ?? false, function ($query) use ($params) {
                $query->whereLike('shop_coupons.title', $params['title']);
            })
            ->when($params['username'] ?? false, function ($query) use ($params) {
                $query->whereLike('members.username', $params['username']);
            })
            ->when(isset($params['status']), function ($query) use ($params) {
                if (is_numeric($params['status'])) {
                    $query->where('shop_user_has_coupons.status', $params['status']);
                }
            })
            ->when($params['start_at'] ?? false, function ($query) use ($params) {
                $query->whereLike('shop_user_has_coupons.created_at', '>=', strtotime($params['start_at']));
            })
            ->when($params['end_at'] ?? false, function ($query) use ($params) {
                $query->whereLike('shop_user_points_log.created_at', '<=', strtotime('+1 day', strtotime($params['end_at'])));
            })
            ->orderByDesc('shop_user_has_coupons.id')
            ->paginate($params['limit'] ?? 10);
    }

    protected function serializeDate(\DateTimeInterface $date): ?string
    {
        return Carbon::instance($date)->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
    }
}
