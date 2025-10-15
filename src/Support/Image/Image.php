<?php

namespace Xditn\Support\Image;

class Image
{
    public function __construct(protected string $path) {}

    /**
     * @param string $path
     * @return self
     */
    public static function of(string $path): Image
    {
        return new self($path);
    }

    /**
     * @return Response
     */
    public function response(): Response
    {
        return new Response($this->path);
    }

    /**
     * 水印
     *
     * @param string $watermarkPath
     * @return Watermark
     */
    public function watermark(string $watermarkPath): Watermark
    {
        return new Watermark($this->path, $watermarkPath);
    }


    /**
     * 添加文字
     *
     * @param string $text
     * @return WarpText
     */
    public function text(string $text): WarpText
    {
        return new WarpText($this->path, $text);
    }

    /**
     * 图片信息
     *
     * @return Meta
     */
    public function meta(): Meta
    {
        return new Meta($this->path);
    }

    /**
     * 调整图片尺寸
     *
     * @param int|null $width
     * @param int|null $height
     * @return Resize
     */
    public function resize(?int $width = null, ?int $height = null): Resize
    {
        return new Resize($this->path, $width, $height);
    }
}
