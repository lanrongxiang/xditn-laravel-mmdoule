<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Cms\Models;

use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $name
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class Tag extends Model
{
    protected $table = 'cms_tags';

    protected $fillable = ['id', 'name', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    protected array $fields = ['id', 'name', 'created_at', 'updated_at'];

    protected array $form = ['name'];

    public array $searchable = [

    ];
}
