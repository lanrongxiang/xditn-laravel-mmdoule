<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Member\Models;

use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $name
 * @property $description
 * @property $status
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class MemberGroups extends Model
{
    protected $table = 'member_groups';

    protected $fillable = ['name', 'description', 'status', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    protected array $fields = ['id', 'name', 'description', 'status', 'created_at', 'updated_at'];

    protected array $form = ['name', 'description', 'status'];

    public array $searchable = [
        'name' => 'like',
        'status' => '=',
    ];
}
