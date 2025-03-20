<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Shop\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $title
 * @property $bill_type
 * @property $delivery_area
 * @property $sort
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class ShipTemplate extends Model
{
    protected $table = 'shop_ship_template';

    protected $fillable = ['id', 'title', 'bill_type', 'delivery_areas', 'sort', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * @var array
     */
    protected array $fields = ['id', 'title', 'bill_type', 'sort', 'created_at'];

    /**
     * @var array
     */
    protected array $form = ['title', 'bill_type', 'delivery_areas', 'sort'];

    /**
     * @var array
     */
    public array $searchable = [
        'title' => 'like',
    ];

    public const BILL_TYPE_PIECE = 1;

    public const BILL_TYPE_WEIGHT = 2;

    public const BILL_TYPE_VOLUME = 3;

    public function deliveryAreas(): Attribute
    {
        return new Attribute(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value)
        );
    }
}
