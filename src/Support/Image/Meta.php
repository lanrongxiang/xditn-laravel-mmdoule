<?php

namespace Xditn\Support\Image;

use Illuminate\Support\Number;

/**
 * 图片信息
 */
class Meta extends AbstractImage
{
    public function __construct(
        protected string $path
    ) {
        parent::__construct();
    }

    /**
     * 格式化
     */
    public function data(): array
    {
        $exif = $this->original();

        return [
            'filename' => $exif->get('FILE.FileName'),
            'size' => Number::fileSize($exif->get('FILE.FileSize')),
            'size_bytes' => $exif->get('FILE.FileSize'),
            'datetime' => date('Y-m-d H:i:s', $exif->get('FILE.FileDateTime')),
            'width' => $exif->get('COMPUTED.Width'),
            'height' => $exif->get('COMPUTED.Height'),
            'mimetype' => $exif->get('FILE.MimeType'),
        ];
    }

    /**
     * 原始信息
     */
    public function original(): mixed
    {
        return $this->read($this->path)->exif();

    }
}
