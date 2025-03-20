<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Shop\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Xditn\Base\modules\Shop\Enums\ProductStatus;
use Xditn\Base\modules\Shop\Enums\ProductTypes;
use Xditn\Base\modules\Shop\Models\Pivots\CategoryHasProducts;
use Xditn\Base\modules\Shop\Models\Pivots\ProductHasServices;
use Xditn\Base\modules\Shop\Models\Pivots\ProductHasSpecs;
use Xditn\Base\modules\Shop\Models\Pivots\ProductHasTags;
use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $type
 * @property $title
 * @property $keywords
 * @property $subtitle
 * @property $images
 * @property $video
 * @property $brand_id
 * @property $category_id
 * @property $is_available
 * @property $is_schedule
 * @property $schedule_time
 * @property $is_specifications
 * @property $unit
 * @property $sales
 * @property $virtual_sales
 * @property $sort
 * @property $ship_type
 * @property $ship_fee
 * @property $ship_template_id
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class Products extends Model
{
    protected $table = 'shop_products';

    protected $fillable = [
        'id',
        'type',
        'title',
        'keywords',
        'subtitle',
        'images',
        'video',
        'brand_id',
        'category_id',
        'is_available',
        'is_schedule',
        'schedule_time',
        'is_specifications',
        'unit',
        'sales',
        'virtual_sales',
        'sort',
        'ship_type',
        'ship_fee',
        'ship_template_id',
        'product_no',
        'price',
        'list_price',
        'cost_price',
        'weight',
        'volume',
        'stock',
        'alert_stock',
        'creator_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public array $searchable = [
        'title' => 'like',
        'is_available' => '=',
    ];

    // 规格
    public const SIGNAL_SPECIFICATION = 1;  // 单规格

    public const MULTI_SPECIFICATION = 2;  // 多规格

    // 定时上下架
    public const SCHEDULE = 1; // 是

    public const NO_SCHEDULE = 2; // 否

    // 立即上架
    public const AVAILABLE = 1; // 立即上架

    public const NO_AVAILABLE = 2; // 放入仓库

    public const FIXED_SHIP = 1; // 固定运费

    public const SHIP_TEMPLATE = 2; // 运费模版

    /**
     * 产品关联的分类
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, CategoryHasProducts::class, 'product_id', 'category_id');
    }

    /**
     * 商品服务
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(ProductServices::class, ProductHasServices::class, 'product_id', 'service_id');
    }

    /**
     * 商品标签
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ProductTags::class, ProductHasTags::class, 'product_id', 'tag_id');
    }

    /**
     * 商品冗余信息
     */
    public function info(): HasOne
    {
        return $this->hasOne(ProductInfo::class, 'product_id', 'id');
    }

    /**
     * 商品所属品牌
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(ProductBrand::class, 'brand_id', 'id');
    }

    /**
     * 产品规格
     */
    public function specifications(): BelongsToMany
    {
        return $this->belongsToMany(ProductSpec::class, ProductHasSpecs::class, 'product_id', 'spec_id')
            ->groupBy('spec_id');
    }

    public function specificationValues(): BelongsToMany
    {
        return $this->belongsToMany(ProductSpecValues::class, ProductHasSpecs::class, 'product_id', 'spec_value_id');
    }

    /**
     * 商品规格 SKU
     */
    public function skus(): HasMany
    {
        return $this->hasMany(ProductSku::class, 'product_id', 'id');
    }

    /**
     * 是否单规格
     */
    public function isSingleSpecification(): bool
    {
        return $this->is_specifications == self::SIGNAL_SPECIFICATION;
    }

    /**
     * 是否多规格
     */
    public function isMultiSpecification(): bool
    {
        return $this->is_specifications == self::MULTI_SPECIFICATION;
    }

    /**
     * 是否立即上架
     */
    public function isAvailable(): bool
    {
        return ProductStatus::AVAILABLE->assert($this->is_available);
    }

    public function isNoAvailable(): bool
    {
        return ProductStatus::NO_AVAILABLE->assert($this->is_available);
    }

    public function isDelist(): bool
    {
        return ProductStatus::DELIST->assert($this->is_available);
    }

    public function isSoldOut(): bool
    {
        return ProductStatus::SOLD_OUT->assert($this->is_available);
    }

    /**
     * 是否定时上下架
     */
    public function isSchedule(): bool
    {
        return $this->is_schedule == self::SCHEDULE;
    }

    /**
     * 是否是实体商品
     */
    public function isPhysical(): bool
    {
        return ProductTypes::PHYSICAL->assert($this->type);
    }

    /**
     * 是否是虚拟商品
     */
    public function isVirtual(): bool
    {
        return ProductTypes::VIRTUAL->assert($this->type);
    }

    /**
     * 是否是卡密商品
     */
    public function isSerialNumber(): bool
    {
        return ProductTypes::SERIAL_NUMBER->assert($this->type);
    }

    public function scheduleTime(): Attribute
    {
        return new Attribute(
            get: fn ($value) => date('Y-m-d H:i:s', $value)
        );
    }

    /**
     * images attribute
     */
    public function images(): Attribute
    {
        return new Attribute(
            get: fn ($value) => json_decode($value, true),
        );
    }

    public function price(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    public function listPrice(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    public function costPrice(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    public function weight(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    public function volume(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100
        );
    }

    public function hiddenPaginate(): static
    {
        return parent::setPaginate(false);
    }
}
