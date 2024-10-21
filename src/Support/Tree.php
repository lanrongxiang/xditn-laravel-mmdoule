<?php

namespace Xditn\Support;

class Tree
{
    private string $pk; // 主键字段名

    /**
     * Tree 构造函数.
     *
     * @param  string  $pk 主键字段名
     */
    public function __construct(string $pk = 'id')
    {
        $this->pk = $pk;
    }

    /**
     * 从扁平数组构建树形结构.
     *
     * @param  array  $items    扁平数组，其中每个元素都是一个关联数组
     * @param  int  $pid      父ID，用于构建树形结构的起点
     * @param  string  $pidField 父ID字段名
     * @param  string  $child    子节点存储的字段名
     * @return array 树形结构
     */
    public static function done(array $items, int $pid = 0, string $pidField = 'parent_id', string $child = 'children'): array
    {
        $tree = []; // 初始化树形结构数组
        // 遍历扁平数组
        foreach ($items as $item) {
            // 如果当前项的父ID等于传入的父ID
            if ($item[$pidField] == $pid) {
                // 递归调用done方法构建子节点数组
                $children = self::done($items, $item[self::pk], $pidField, $child);
                // 如果子节点数组不为空，则将其添加到当前项中
                if (! empty($children)) {
                    $item[$child] = $children;
                }
                // 将当前项添加到树形结构数组中
                $tree[] = $item;
            }
        }

        return $tree; // 返回树形结构数组
    }
}
