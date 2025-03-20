<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Shop\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $name
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class ProductSpecTmp extends Model
{
    protected $table = 'shop_product_spec_tmp';

    protected $fillable = ['id', 'name', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    protected array $fields = ['id', 'name', 'created_at', 'updated_at'];

    protected array $form = ['name'];

    public array $searchable = [
        'name' => 'like',
    ];

    /**
     * 模版规格
     */
    public function specs(): HasMany
    {
        return $this->hasMany(ProductSpec::class, 'spec_tmp_id', 'id');
    }
}
