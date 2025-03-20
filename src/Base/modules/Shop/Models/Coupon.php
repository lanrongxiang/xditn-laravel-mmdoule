<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Shop\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Xditn\Base\modules\Member\Models\Members;
use Xditn\Base\modules\Shop\Enums\CouponExpireType;
use Xditn\Base\modules\Shop\Enums\CouponScope;
use Xditn\Base\modules\Shop\Enums\CouponType;
use Xditn\Base\modules\Shop\Enums\CouponUserStatus;
use Xditn\Base\XditnModel as Model;
use Xditn\Exceptions\FailedException;

/**
 * @property $id
 * @property $title
 * @property $type
 * @property $reduce_price
 * @property $discount
 * @property $min_price
 * @property $expire_type
 * @property $validaty
 * @property $start_at
 * @property $end_at
 * @property $scope
 * @property $scope_data
 * @property $total_num
 * @property $receive_num
 * @property $describe
 * @property $status
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class Coupon extends Model
{
    protected $table = 'shop_coupons';

    protected $fillable = ['id', 'title', 'type', 'reduce_price', 'discount', 'min_price', 'expire_type', 'validaty', 'start_at', 'end_at', 'scope', 'scope_data', 'total_num', 'receive_num', 'describe', 'status', 'sort', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    protected array $fields = ['id', 'title', 'type', 'reduce_price', 'discount', 'min_price', 'expire_type', 'validaty', 'start_at', 'end_at', 'scope', 'scope_data', 'total_num', 'receive_num', 'describe', 'status', 'created_at', 'sort'];

    protected array $form = ['title', 'type', 'reduce_price', 'discount', 'min_price', 'expire_type', 'scope_data', 'total_num', 'receive_num', 'status', 'sort'];

    public array $searchable = [
        'title' => 'like',
        'type' => '=',
        'expire_type' => '=',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(Members::class, 'shop_user_has_coupons', 'coupon_id', 'user_id')
            ->withPivot(['created_at', 'updated_at']);
    }

    /**
     * 获取优惠券未使用的用户
     */
    public function unUsedUsers(array $fields = ['*']): Collection
    {
        return $this->users()->wherePivot('status', CouponUserStatus::UN_USED)->get($fields);
    }

    /**
     * 获取已使用的用户券
     */
    public function usedUsers(array $fields = ['*']): Collection
    {
        return $this->users()->wherePivot('status', CouponUserStatus::USED)->get($fields);
    }

    /**
     * 发放优惠券
     *
     * @return true
     */
    public function giveUsers(int $id, array $userIds): bool
    {
        $coupon = $this->find($id);

        $couponUserIds = $coupon->unUsedUsers()->pluck('id')->toArray();

        if (! empty($couponUserIds)) {
            foreach ($userIds as $k => $userId) {
                if (in_array($userId, $couponUserIds)) {
                    unset($userIds[$k]);
                }
            }
        }

        $leftNum = $coupon->leftNum();
        if (! $leftNum || $leftNum < count($userIds)) {
            throw new FailedException('优惠券发放数量不足');
        }

        $this->transaction(function () use ($coupon, $userIds) {
            // 发放优惠券
            $coupon->users()->attach($userIds, [
                'created_at' => time(),
                'updated_at' => time(),
            ]);

            // 增加领取数量
            $coupon->receive_num += count($userIds);
            $coupon->save();

            return true;
        });

        return true;
    }

    public function reducePrice(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $this->isDiscount() ? 0 : $value * 100
        );
    }

    public function discount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 10,
            set: fn ($value) => $this->isFullReduce() ? 0 : $value * 10
        );
    }

    public function minPrice(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    public function validaty(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $this->isFixTime() ? 0 : (int) $value
        );
    }

    public function leftNum(): mixed
    {
        return $this->total_num - $this->receive_num;
    }

    public function startAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? date('Y-m-d', $value) : 0,
            set: fn ($value) => $this->isReceiveEffect() ? 0 : ($value ? strtotime($value) : 0)
        );
    }

    public function endAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? date('Y-m-d', $value) : 0,
            set: fn ($value) => $this->isReceiveEffect() ? 0 : ($value ? strtotime($value) + 86400 : 0)
        );
    }

    public function scopeData(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? json_decode($value, true) : [],
            set: fn ($value) => $this->isFull() ? json_encode([]) : ($value ? json_encode($value) : json_encode([]))
        );
    }

    /**
     * 是否是折扣券
     */
    public function isDiscount(): bool
    {
        return CouponType::DISCOUNT->assert($this->type);
    }

    /**
     * 是否是满减券
     */
    public function isFullReduce(): bool
    {
        return CouponType::FULL_REDUCE->assert($this->type);
    }

    /**
     * 是否是固定时间
     */
    public function isFixTime(): bool
    {
        return CouponExpireType::FIX_TIME->assert($this->expire_type);
    }

    /**
     * 是否是领取后生效
     */
    public function isReceiveEffect(): bool
    {
        return CouponExpireType::RECEIVE_EFFECT->assert($this->expire_type);
    }

    /**
     * 是否是全场通用
     */
    public function isFull(): bool
    {
        return CouponScope::FULL->assert($this->scope);
    }

    /**
     * 是否是指定商品
     */
    public function isSpecifyProducts(): bool
    {
        return CouponScope::PRODUCTS->assert($this->scope);
    }
}
