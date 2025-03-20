<?php

namespace Xditn\Support;

/**
 * Tree 类用于将数据组织成树形结构
 */
class Tree
{
    protected static string $pk = 'id'; // 主键字段名称

    /**
     * 设置主键字段
     *
     * @param  string  $pk 主键字段名
     * @return Tree 返回当前 Tree 对象
     */
    public static function setPk(string $pk): Tree
    {
        self::$pk = $pk;

        return new self();
    }

    /**
     * 将数据转换为树形结构
     *
     * @param  array  $items 数据数组
     * @param  int  $pid 父级 ID
     * @param  string  $pidField 父级 ID 字段
     * @param  string  $child 子节点字段名
     * @return array 返回树形结构的数组
     */
    public static function done(array $items, int $pid = 0, string $pidField = 'parent_id', string $child = 'children'): array
    {
        $tree = [];

        foreach ($items as $item) {
            if ($item[$pidField] == $pid) {
                // 递归调用，获取当前项的子节点
                $children = self::done($items, $item[self::$pk], $pidField, $child);

                // 如果有子节点，添加到当前项的子节点字段中
                if (! empty($children)) {
                    $item[$child] = $children;
                }

                // 将当前项添加到树结构中
                $tree[] = $item;
            }
        }

        return $tree;
    }
}
