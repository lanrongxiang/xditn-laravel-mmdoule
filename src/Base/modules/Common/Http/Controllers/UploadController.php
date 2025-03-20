<?php

namespace Xditn\Base\modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\Common\Support\Upload\Uploader;
use Xditn\Base\modules\System\Support\Upload;

/**
 * @group 公共模块
 *
 * @subgroup 上传功能
 * @subgroupDescription MModule 上传
 */
class UploadController
{
    /**
     * 文件上传
     *
     * @bodyParam category_id int required 分类ID
     * @bodyParam `file` file required 文件
     *
     * @responseField driver string 上传驱动名称
     * @responseField path string 上传路径(相对路径，如：/uploads/attachments/2024-09-17/2024V7zVurk7AU1726539554.png)
     * @responseField originName string 原文件名
     * @responseField size int 文件大小
     * @responseField type string 文件类型
     * @responseField ext string 文件扩展名
     * @responseField category_id int 分类ID
     *
     * @param  Request  $request
     * @param  Uploader  $uploader
     * @return array
     */
    public function file(Request $request, Uploader $uploader): array
    {
        return $uploader->withCategoryId($request->get('category_id', 0))
            ->upload($request->file('file'));
    }

    /**
     * 图片上传
     *
     * @bodyParam category_id int required 分类ID
     * @bodyParam `image` file required 图片
     *
     * @responseField driver string 上传驱动名称
     * @responseField path string 上传路径(相对路径，如：/uploads/attachments/2024-09-17/2024V7zVurk7AU1726539554.png)
     * @responseField originName string 原文件名
     * @responseField size int 文件大小
     * @responseField type string 文件类型
     * @responseField ext string 文件扩展名
     * @responseField category_id int 分类ID
     *
     * @param  Request  $request
     * @param  Uploader  $uploader
     * @return array
     */
    public function image(Request $request, Uploader $uploader): array
    {
        return $uploader->withCategoryId($request->get('category_id', 0))
            ->upload($request->file('image'));
    }

    /**
     * 第三方上传 token
     *
     * @queryParam driver string required 上传驱动名称
     *
     * @param  Request  $request
     * @return array
     */
    public function token(Request $request): array
    {
        $upload = new Upload();
        $method = $request->get('driver').'Token';

        return $upload->{$method}($request->get('filename'));
    }
}
