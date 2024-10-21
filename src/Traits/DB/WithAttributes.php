<?php

namespace Xditn\Traits\DB;

/**
 * 属性操作基础 Trait
 */
trait WithAttributes
{
    // 父级 ID 列名
    protected string $parentIdColumn = 'parent_id';

    // 排序字段
    protected string $sortField = '';

    // 是否降序排序
    protected bool $sortDesc = true;

    // 是否以树结构显示
    protected bool $asTree = false;

    // 列表显示的字段
    protected array $fields = ['*'];

    // 是否分页
    protected bool $isPaginate = true;

    // 表单关联
    protected array $formRelations = [];

    // 数据范围
    protected bool $dataRange = false;

    // 是否将 null 转为空字符串
    protected bool $autoNull2EmptyString = true;

    // 是否填充创建人 ID
    protected bool $isFillCreatorId = true;
}
