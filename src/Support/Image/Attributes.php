<?php
namespace Xditn\Support\Image;

use Intervention\Image\Format;

/**
 * 图片属性
 */
trait Attributes
{
    /**
     * 设置图片高度
     */
    protected ?int $width = null;

    /**
     * 设置图片宽度
     */
    protected ?int $height = null;

    /**
     * 设置图片质量
     */
    protected int $quality = 90;

    /**
     * 设置图片格式
     */
    protected Format $format = Format::WEBP;



    /**
     * 设置图片宽度
     *
     * @return $this
     */
    public function width(int $width): static
    {
        $this->width = $width;

        return $this;
    }

    /**
     * 设置图片高度
     *
     * @return $this
     */
    public function height(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    /**
     * 设置图片质量
     *
     * @return $this
     */
    public function quality(int $quality): static
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * 设置图片格式
     *
     * @return $this
     */
    public function format(Format|string $format): static
    {
        if (is_string($format)) {
            $format = Format::create($format);
        }

        $this->format = $format;

        return $this;
    }

}
