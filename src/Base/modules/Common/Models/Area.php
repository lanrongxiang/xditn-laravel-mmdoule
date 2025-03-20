<?php

namespace Xditn\Base\modules\Common\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Xditn\Exceptions\FailedException;

class Area extends Model
{
    protected $table = 'areas';

    public function getAll()
    {
        if (! Schema::hasTable('areas')) {
            throw new FailedException('请使用 php artisan xditn:areas 获取地区数据源');
        }

        return $this->whereIn('level', [1, 2])->get(['id', 'parent_id', 'name'])->toTree(0, 'parent_id')
                        ->filter(function ($area) {
                            return isset($area['children']);
                        })->values();
    }
}
