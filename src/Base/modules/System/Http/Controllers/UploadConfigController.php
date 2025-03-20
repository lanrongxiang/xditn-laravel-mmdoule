<?php

declare(strict_types=1);

namespace Xditn\Base\modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Xditn\Base\modules\System\Models\SystemConfig;
use Xditn\Base\modules\System\Support\Configure;
use Xditn\Base\XditnController as Controller;
use Xditn\Exceptions\FailedException;

class UploadConfigController extends Controller
{
    public function __construct(
        protected readonly SystemConfig $model
    ) {
    }

    public function store(Request $request)
    {
        $driver = $request->get('driver');
        if (! $driver) {
            throw new FailedException('请先选择上传驱动');
        }

        $config = Configure::parse('upload', $request->only(['file_exts', 'image_exts', 'limit_size']));
        $config = array_merge($config, Configure::parse("upload.$driver", $request->except(['file_exts', 'image_exts', 'limit_size', 'driver'])));
        $config['upload.driver'] = $driver;

        return $this->model->storeBy($config);
    }

    public function show($driver = null)
    {
        if (! $driver) {
            $driver = config('upload.driver');
        }

        $fileExts = config()->get('upload.file_exts');
        $imageExts = config()->get('upload.image_exts');
        return [
            'driver' => $driver,
            'limit_size' => (int) config('upload.limit_size', 1),
            'file_exts' => $fileExts,
            'image_exts' => $imageExts,
            'config' => config("upload.$driver"),
        ];
    }
}
