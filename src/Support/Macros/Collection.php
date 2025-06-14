<?php

declare(strict_types=1);

namespace Xditn\Support\Macros;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection as LaravelCollection;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Xditn\Support\Tree;
use Xditn\Support\Excel\Csv;
use Xditn\Support\Excel\Export;

/**
 * 集合宏扩展
 */
class Collection
{
    public function boot(): void
    {
        $this->toOptions();
        $this->toTree();
        $this->export();

        $this->download();

        $this->downloadAsCsv();
    }

    /**
     * 将集合转换为树形结构
     *
     * @return void
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
     * @return void
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

    /**
     * 导出数据
     *
     * @return void
     */
    public function export(): void
    {
        LaravelCollection::macro(__FUNCTION__, function (array $header) {
            $items = $this->toArray();
            $export = new class($items, $header) extends Export
            {
                protected array $items;

                public function __construct(array $items, array $header)
                {
                    $this->items = $items;

                    $this->header = $header;
                }

                public function array(): array
                {
                    // TODO: Implement array() method.
                    return $this->items;
                }
            };

            return $export->export();
        });
    }

    /**
     * 根据字段导出
     *
     * @return void
     */
    public function download(): void
    {
        LaravelCollection::macro(__FUNCTION__, function (array $header, array $fields = []) {
            $items = $this->toArray();
            // 自定字段重新组装数据
            $newItems = [];
            if (! empty($fields)) {
                foreach ($items as $item) {
                    $newItem = [];
                    foreach ($fields as $field) {
                        $newItem[] = $item[$field] ?? null;
                    }
                    $newItems[] = $newItem;
                }
            }
            if (count($newItems)) {
                $items = $newItems;
            }

            $export = new class($items, $header) extends Export
            {
                protected array $items;

                public function __construct(array $items, array $header)
                {
                    $this->items = $items;

                    $this->header = $header;
                }

                public function array(): array
                {
                    // TODO: Implement array() method.
                    return $this->items;
                }
            };

            return $export->download();
        });
    }

    /**
     * 下载 csv
     */
    public function downloadAsCsv(): void
    {
        LazyCollection::macro(__FUNCTION__, function (array $header, ?string $filename = null) {
            $csv = new Csv;

            $filename = $filename ?: Str::random(10).'.csv';

            return $csv->header($header)->download($filename, $this);
        });
    }
}
