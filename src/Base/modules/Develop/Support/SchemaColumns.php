<?php

namespace Xditn\Base\modules\Develop\Support;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SchemaColumns
{
    // 前端组件常量定义 基础版
    public const DATE_PICKER  = 'DatePicker';
    public const INPUT        = 'Input';
    public const INPUT_NUMBER = 'InputNumber';
    public const RADIO_GROUP  = 'RadioGroup';
    public const SELECT       = 'Select';
    public const SELECT_GROUP = 'SelectGroup';
    public const SPACE        = 'Space';
    public const SWITCH       = 'Switch';
    public const TIME_PICKER  = 'TimePicker';
    public const TREE_SELECT  = 'TreeSelect';

    // 数据库字段类型组定义
    private const COMPONENT_MAP = [
        'number'   => ['tinyint', 'smallint', 'integer', 'mediumint', 'int', 'bigint', 'float', 'double', 'decimal'],
        'string'   => ['string', 'char', 'varchar', 'tinytext'],
        'text'     => ['text', 'mediumtext', 'longtext'],
        'date'     => ['date'],
        'datetime' => ['datetime', 'timestamp'],
        'boolean'  => ['boolean'],
    ];

    private const TYPE_COMPONENT_MAP = [
        'number'   => self::INPUT_NUMBER,
        'string'   => self::INPUT,
        'text'     => self::INPUT,
        'date'     => self::DATE_PICKER,
        'datetime' => self::TIME_PICKER,
        'boolean'  => self::SWITCH,
    ];

    public function parse(array $columns)
    {
        return array_map(function ($column)
        {
            // 解析注释获取标签和选项
            $options         = $this->parseComment($column['comment']);
            $column['label'] = $this->getLabel($column);
            // 如果存在选项且未指定 component，则根据选项数量选择合适的组件
            if (!empty($options)) {
                $column['options'] = $options;
                // 根据选项数量设置组件
                $column['component'] = $this->selectOrRadioComponent($options);
            } else {
                // 如果没有选项，按字段类型决定组件
                $column['component'] = $this->determineComponent($column);
            }
            // 设置表单显示条件
            $column['form'] = $this->isVisibleInForm($column['name']);
            return $column;
        }, $columns);
    }

    protected function parseComment(?string $comment): array
    {
        $options = [];
        if (!$comment) {
            return $options;
        }
        // 解析格式 "1 目录 2 菜单 3 按钮"
        if (preg_match_all('/\s/', $comment) >= 3) {
            $items = preg_split('/\s+/', $comment);
            // 每对 "值 标签" 组合
            for ($i = 0; $i < count($items); $i += 2) {
                if (isset($items[$i + 1])) {
                    $options[] = [
                        'value' => $items[$i],
                        'label' => $items[$i + 1],
                    ];
                }
            }
        }
        return $options;
    }

    protected function determineComponent(array $column): string
    {
        if (Str::endsWith($column['name'], '_id')) {
            return $this->determineForeignKeyComponent($column['name']);
        }
        foreach (self::COMPONENT_MAP as $type => $dbTypes) {
            if (in_array($column['type'], $dbTypes)) {
                return self::TYPE_COMPONENT_MAP[$type];
            }
        }
        return self::INPUT; // 默认使用 Input 组件
    }

    protected function determineForeignKeyComponent(string $columnName): string
    {
        $tableName = Str::remove('_id', $columnName);
        if (Schema::hasTable($tableName) && in_array('parent_id', Schema::getColumnListing($tableName))) {
            return self::TREE_SELECT;
        }
        return self::SELECT_GROUP;
    }

    protected function selectOrRadioComponent(array $options): string
    {
        return count($options) <= 5 ? self::RADIO_GROUP : self::SELECT_GROUP;
    }

    protected function getLabel(array $colum): string
    {
        return $colum['comment'] ? explode(':', $colum['comment'])[0] : $colum['name'];
    }

    protected function isVisibleInForm(string $columnName): bool
    {
        return !in_array($columnName, ['id', 'created_at', 'updated_at', 'deleted_at']);
    }
}
