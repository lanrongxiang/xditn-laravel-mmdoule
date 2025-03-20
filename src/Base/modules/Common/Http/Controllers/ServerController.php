<?php

namespace Xditn\Base\modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Xditn\MModule;

/**
 * @group 公共模块
 *
 * @subgroup 服务器信息
 * @subgroupDescription MModule 后台服务器信息
 */
class ServerController
{
    /**
     * 服务器信息
     *
     * @responseField os string 操作系统
     * @responseField php string PHP版本
     * @responseField laravel string Laravel版本
     * @responseField xditn_MModule string MModule版本
     * @responseField host string 域名
     * @responseField mysql string MySQL版本
     * @responseField memory_limit string 内存限制
     * @responseField max_execution_time string 最大执行时间
     * @responseField upload_max_filesize string 上传文件大小
     *
     * @param  Request  $request
     * @return array
     */
    public function info(Request $request)
    {
        $version = json_decode(json_encode(DB::select('select version()')), true);

        return [
            'os' => PHP_OS_FAMILY,
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'xditn_MModule' => MModule::version(),
            'host' => $request->host(),
            'mysql' => $version[0]['version()'],
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time').'/S',
            'upload_max_filesize' => ini_get('upload_max_filesize'),
        ];
    }
}
