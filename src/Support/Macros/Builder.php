<?php

declare(strict_types=1);

namespace Xditn\Support\Macros;

use Illuminate\Database\Eloquent\Builder as LaravelBuilder;
use Illuminate\Support\Str;
use Xditn\Support\DB\SoftDelete;

/**
 * Eloquent 查询构建器扩展
 */
class Builder
{
    /**
     * 启动宏
     */
    public function boot(): void
    {
        $this->whereLike();
        $this->quickSearch();
        $this->tree();
    }

    /**
     * 添加 where like 查询
     */
    public function whereLike(): void
    {
        LaravelBuilder::macro(__FUNCTION__, function ($field, $value) {
            /** @var LaravelBuilder $this */
            return $this->where($field, 'like', "%$value%");
        });
    }

    /**
     * 快速搜索
     */
    public function quickSearch(): void
    {
        LaravelBuilder::macro(__FUNCTION__, function (array $params = []) {
            /** @var LaravelBuilder $this */
            // 合并请求参数和传入的参数
            $params = array_merge(request()->all(), $params);

            // 检查模型中是否存在 searchable 属性
            if (! property_exists($this->model, 'searchable')) {
                return $this;
            }

            // 过滤掉 null、空字符串和空数组的参数
            $params = array_filter($params, function ($value) {
                return (is_string($value) && strlen($value)) || is_numeric($value) || (is_array($value) && ! empty($value));
            });


            $wheres = [];
            // 遍历模型中的 searchable 字段
            foreach ($this->model->searchable as $field => $op) {
                $_field = $field;

                // 如果字段包含别名，去除别名部分
                if (str_contains($field, '.')) {
                    [, $_field] = explode('.', $field);
                }
                // 如果请求参数中存在对应的字段值，构建查询条件
                if (array_key_exists($_field, $params)) {
                    $searchValue = $params[$_field];
                    $operate = Str::of($op)->lower();
                    $value = $searchValue;

                    // 根据操作符调整查询值
                    if ($operate->exactly('op')) {
                        $value = implode(',', $searchValue);
                    } elseif ($operate->exactly('like')) {
                        $value = "%{$searchValue}%";
                    } elseif ($operate->exactly('rlike')) {
                        $op = 'like';
                        $value = $searchValue.'%';
                    } elseif ($operate->exactly('llike')) {
                        $op = 'like';
                        $value = '%'.$searchValue;
                    }

                    // 特殊处理 _at 和 _time 结尾的字段
                    if (Str::of($_field)->endsWith('_at') || Str::of($_field)->endsWith('_time')) {
                        $value = is_string($searchValue) ? strtotime($searchValue) : $searchValue;
                    }

                    // 将条件加入到查询数组中
                    $wheres[] = [$field, strtolower($op), $value];
                }
            }
            // 组装 where 查询条件
            foreach ($wheres as $w) {
                [$field, $op, $value] = $w;
                if ($op == 'in') {
                    $this->whereIn($field, is_array($value) ? $value : explode(',', $value));
                } elseif ($op == 'between') {
                    $this->whereBetween($field, $value);
                } else {
                    $this->where($field, $op, $value);
                }
            }

            return $this;
        });
    }

    /**
     * 添加树形查询
     */
    public function tree(): void
    {
        LaravelBuilder::macro(__FUNCTION__, function (string $id, string $parentId, ...$fields) {
            $fields = array_merge([$id, $parentId], $fields);

            return $this->get($fields)->toTree(0, $parentId);
        });
    }

    public function restores(): void
    {
        LaravelBuilder::macro(__FUNCTION__, function () {
            return $this->withoutGlobalScope(SoftDelete::class)->update([
                'deleted_at' => 0,
            ]);
        });
    }
}
