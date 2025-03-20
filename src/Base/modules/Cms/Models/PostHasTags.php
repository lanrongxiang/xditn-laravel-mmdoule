<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Cms\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PostHasTags extends Pivot
{
    protected $table = 'cms_post_has_tags';
}
