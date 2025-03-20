<?php

namespace Xditn\Base\modules\Cms\Models;

use Illuminate\Database\Eloquent\Collection;
use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $name
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 */
class Tags extends Model
{
    protected $table = 'cms_tags';

    protected $fillable = [
        'id', 'name', 'creator_id', 'created_at', 'updated_at', 'deleted_at',
    ];

    /**
     * get tags
     */
    public static function getTagsByNames(array $names): Collection
    {
        return self::query()->whereIn('name', $names)->get();
    }
}
