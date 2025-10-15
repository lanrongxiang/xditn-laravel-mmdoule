<?php

namespace Xditn\Support\Image;

use Illuminate\Support\Number;
use Intervention\Image\Format;

/**
 * 图片响应
 *
 * @method mixed
 * Class Image
 */
class Response extends AbstractImage
{
    public function __construct(protected string $path)
    {
        parent::__construct();
    }

    /**
     * 响应WEBP图片
     */
    public function webp(): mixed
    {
        return $this->format(Format::WEBP)->response();
    }

    /**
     * png 图片响应
     */
    public function png(): mixed
    {
        return $this->format(Format::PNG)->response();
    }

    /**
     * jepg 图片响应
     */
    public function jpeg(): mixed
    {
        return $this->format(Format::JPEG)->response();
    }

    /**
     * Gif 图片响应
     */
    public function gif(): mixed
    {
        return $this->format(Format::GIF)->response();
    }

    public function __call($method, $arguments)
    {
        return $this->format(Format::create($method))->response();
    }

    protected function response(): mixed
    {
        $image = $this->read($this->path)->scale($this->width, $this->height);

        return response()->image($image, $this->format, quality: $this->quality);
    }

    public function responsing() {}
}
