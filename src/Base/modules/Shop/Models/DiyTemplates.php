<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Shop\Models;

use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $title
 * @property $type
 * @property $content
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class DiyTemplates extends Model
{
    protected $table = 'shop_diy_templates';

    protected $fillable = ['id', 'title', 'type', 'content', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * @var array
     */
    protected array $fields = ['id', 'title', 'type', 'content', 'created_at'];

    /**
     * @var array
     */
    protected array $form = ['title', 'type', 'content'];

    /**
     * @var array
     */
    public array $searchable = [
        'title' => 'like',

    ];
}
