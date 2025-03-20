<?php

namespace Xditn\Support;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class IP
{
    public static function getLocation(string $ip): string
    {
        // 百度 IP 查询 API
        $url = "https://opendata.baidu.com/api.php?query=$ip&co=&resource_id=6006&oe=utf8";
        try {
            $response = Http::timeout(2)->get($url)->throw();

            if ($response['status']) {
                return '未知';
            }

            $location = $response['data'][0]['location'] ?? '未知';

            return Str::of($location)
                ->replace(['电信', '移动', '联通'], ['', '', ''])
                ->trim()
                ->toString();

        } catch (Throwable|Exception $e) {
            return '未知';
        }
    }
}
