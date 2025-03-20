<?php

declare(strict_types=1);

namespace Xditn\Base\modules\System\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $name
 * @property $key
 * @property $status
 * @property $description
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class Dictionary extends Model
{
    protected $table = 'system_dictionary';

    protected $fillable = ['id', 'name', 'key', 'status', 'description', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    protected array $fields = ['id', 'name', 'key', 'status', 'description', 'created_at', 'updated_at'];

    protected array $form = ['name', 'key', 'status', 'description'];

    public array $searchable = [
        'name' => 'like',
        'key' => 'like',
        'status' => '=',
    ];

    /**
     * 字典值集合
     */
    public function values(): HasMany
    {
        return $this->hasMany(DictionaryValues::class, 'dic_id', 'id');
    }
}
