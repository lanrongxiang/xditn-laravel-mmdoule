<?php

namespace Xditn\Support\DB;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * 数据库查询日志记录类
 */
class Query
{
    protected static string|null $log = null;

    /**
     * 监听查询
     */
    public static function listen(): void
    {
        DB::listen(function ($query) {
            // 获取原始SQL和绑定参数
            $rawSql = $query->sql;
            $bindings = $query->bindings;

            $fullSql = self::safeBuildSql($rawSql, $bindings);

            $logEntry = sprintf(
                '[%s] %s | %s ms',
                date('Y-m-d H:i'),
                $fullSql,
                $query->time
            );

            static::$log .= $logEntry . PHP_EOL;
        });
    }

    /**
     * 安全构建SQL语句
     */
    protected static function safeBuildSql(string $sql, array $bindings): string
    {
        // 处理数字参数
        $numericBindings = array_filter($bindings, 'is_numeric');

        // 处理字符串参数（转义特殊字符）
        $stringBindings = array_map(function ($value) {
            if ($value instanceof \DateTimeInterface) {
                return DB::getPdo()->quote($value->format('Y-m-d H:i:s'));
            }

            if (is_string($value)) {
                // 使用PDO安全引用字符串
                return DB::getPdo()->quote($value);
            }

            if (is_object($value) && method_exists($value, '__toString')) {
                return DB::getPdo()->quote((string)$value);
            }

            if (is_null($value)) {
                return 'NULL';
            }

            return $value;
        }, $bindings);

        // 合并处理后的参数
        $processedBindings = $stringBindings;

        // 逐个替换占位符
        $result = $sql;
        foreach ($processedBindings as $binding) {
            $result = Str::replaceFirst('?', $binding, $result);
        }

        return $result;
    }


    /**
     * 记录查询日志
     */
    public static function log(): void
    {
        if (static::$log) {
            $sqlLogPath = storage_path('logs'.DIRECTORY_SEPARATOR.'query'.DIRECTORY_SEPARATOR);

            if (! File::isDirectory($sqlLogPath)) {
                File::makeDirectory($sqlLogPath, 0777, true);
                chmod($sqlLogPath, 0777);
            }

            $logFile = $sqlLogPath.date('Ymd').'.log';

            // 首次创建文件时设置权限
            if (!File::exists($logFile)) {
                File::put($logFile, '');
                chmod($logFile, 0666);
            }
            File::append($logFile, static::$log);
        }
    }
}
