<?php

declare(strict_types=1);

namespace Xditn\Support\Macros;

use Illuminate\Support\Collection as LaravelCollection;
use Xditn\Support\Tree;

/**
 * 集合宏扩展
 */
class Collection
{
    public function boot(): void
    {
        $this->toOptions();
        $this->toTree();
    }

    /**
     * 将集合转换为树形结构
     */
    public function toTree(): void
    {
        LaravelCollection::macro(__FUNCTION__, function (int $pid = 0, string $pidField = 'parent_id', string $child = 'children') {
            return LaravelCollection::make(Tree::done($this->all(), $pid, $pidField, $child));
        });
    }

    /**
     * 将集合转换为选项列表
     */
    public function toOptions(): void
    {
        LaravelCollection::macro(__FUNCTION__, function () {
            return $this->transform(function ($item, $key) {
                return ['value' => $item['id'], 'label' => $item['name']];
            });
        });
    }
}
