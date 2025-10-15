<?php

namespace Xditn\Support\Image;

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

/**
 * Class Image
 */
abstract class AbstractImage
{
    use Attributes;
    use ReadFrom;

    protected ImageManager $image;

    /**
     * Image constructor.
     */
    public function __construct()
    {
        if ($this->isGdDriver()) {
            $this->image = new ImageManager(GdDriver::class);
        } else {
            $this->image = new ImageManager(ImagickDriver::class);
        }
    }

    /**
     * 是否是使用 GD 驱动
     */
    protected function isGdDriver(): bool
    {
        return config('xditn.image.driver', 'gd') === 'gd';
    }

    /**
     * 读取图片
     */
    public function read(string $imagePath): ImageInterface
    {
        return $this->image->read($this->getRealPath($imagePath));
    }

    /**
     * 获取图像处理
     */
    public function getImageDriver(): ImageManager
    {
        return $this->image;
    }
}
