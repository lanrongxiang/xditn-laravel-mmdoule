<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Shop\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $activity_start_date
 * @property $activity_end_date
 * @property $activity_events
 * @property $status
 * @property $seckill_product_id
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class SeckillActivity extends Model
{
    protected $table = 'shop_seckill_activity';

    protected $fillable = ['id', 'activity_start_date', 'activity_end_date', 'activity_events', 'status', 'seckill_product_id', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    protected array $fields = ['id', 'activity_start_date', 'activity_end_date', 'activity_events', 'status', 'seckill_product_id', 'created_at'];

    protected array $form = ['activity_start_date', 'activity_end_date', 'activity_events', 'status', 'seckill_product_id'];

    /**
     * 秒杀活动场次
     *
     * @return Attribute
     */
    public function activityEvents(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value)
        );
    }

    /**
     * 活动是否开始
     *
     * @return bool
     */
    public function isStart(): bool
    {
        return time() > strtotime($this->activity_start_date);
    }

    /**
     * 活动是否结束
     *
     * @return bool
     */
    public function isOver(): bool
    {
        return time() > strtotime('+1 day', strtotime($this->activity_end_date));
    }

    /**
     * @return bool
     */
    public function isProcessing(): bool
    {
        return $this->isStart() && ! $this->isOver();
    }
}
