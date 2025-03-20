<?php

declare(strict_types=1);

namespace Xditn\Support\Macros;

use Illuminate\Contracts\Support\Arrayable;
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
     *
     * @param  int  $pid 父级 ID
     * @param  string  $pidField 父级字段名
     * @param  string  $child 子节点字段名
     * @return LaravelCollection
     */
    public function toTree(): void
    {
        LaravelCollection::macro(
            __FUNCTION__,
            function (int $pid = 0, string $pidField = 'parent_id', string $child = 'children') {
                /** @var LaravelCollection $this */
                return LaravelCollection::make(Tree::done($this->all(), $pid, $pidField, $child));
            }
        );
    }

    /**
     * 转换集合为选项数组
     *
     * @return LaravelCollection
     */
    public function toOptions(): void
    {
        LaravelCollection::macro(__FUNCTION__, function () {
            /** @var LaravelCollection $this */
            return $this->transform(function ($item, $key) {
                // 将对象转换为数组
                if ($item instanceof Arrayable) {
                    $item = $item->toArray();
                }

                // 返回选项数组
                return is_array($item)
                    ? ['value' => $item[0], 'label' => $item[1]]
                    : ['value' => $key, 'label' => $item];
            })->values();
        });
    }
}
