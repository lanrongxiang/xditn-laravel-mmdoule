<?php

declare(strict_types=1);

namespace Xditn\Support\Macros;

use Illuminate\Database\Eloquent\Builder as LaravelBuilder;

/**
 * 查询构建器宏扩展
 */
class Builder
{
    public function boot(): void
    {
        $this->whereLike();
        $this->quickSearch();
        $this->tree();
    }

    /**
     * whereLike 宏
     */
    public function whereLike(): void
    {
        LaravelBuilder::macro(__FUNCTION__, function ($field, $value) {
            return $this->where($field, 'like', "%$value%");
        });
    }

    /**
     * 快速搜索宏
     */
    public function quickSearch(): void
    {
        LaravelBuilder::macro(__FUNCTION__, function (array $params = []) {
            $params = array_merge(request()->all(), $params);

            if (! property_exists($this->model, 'searchable')) {
                return $this;
            }

            foreach ($params as $field => $value) {
                if (in_array($field, $this->model->searchable)) {
                    $this->where($field, 'like', "%$value%");
                }
            }

            return $this;
        });
    }

    /**
     * 树形结构查询宏
     */
    public function tree(): void
    {
        LaravelBuilder::macro(__FUNCTION__, function () {
            return $this->orderBy('parent_id', 'asc')
                        ->orderBy('sort', 'desc')
                        ->get()
                        ->toTree();
        });
    }
}
