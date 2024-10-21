<?php

namespace Xditn\Support\DB;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
            $sql = str_replace(
                '?',
                '%s',
                sprintf('[%s] '.$query->sql.' | %s ms'.PHP_EOL, date('Y-m-d H:i'), $query->time)
            );

            static::$log .= vsprintf($sql, $query->bindings);
        });
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
            }

            $logFile = $sqlLogPath.date('Ymd').'.log';

            File::append($logFile, static::$log);
        }
    }
}
