<?php

declare(strict_types=1);

namespace Xditn\Modules\Permissions\Models;

use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $job_name
 * @property $coding
 * @property $status
 * @property $sort
 * @property $description
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
*/
class Jobs extends Model
{
    protected $table = 'station';

    protected $fillable = ['id', 'job_name', 'coding', 'status', 'sort', 'description', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * @var array
     */
    protected array $fields = ['id','job_name','coding','status','sort','description','created_at','updated_at'];

    /**
     * @var array
     */
    protected array $form = ['job_name','coding','status','sort','description'];

    /**
     * @var array
     */
    public array $searchable = [
        'job_name' => 'like'
    ];
}
