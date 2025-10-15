<?php

namespace Xditn\Support\Image;

use Closure;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Interfaces\ImageInterface;
use RuntimeException;

/**
 * 调整图片尺寸
 */
class Resize extends AbstractImage
{
    protected ?Closure $saving = null;

    protected bool $isAllowOriginSize = false;

    /**
     * 调用方法 resize|resizeDown|scale|scaleDown|cover|coverDown|pad|contain|crop|resizeCanvas|resizeCanvasRelative|trim
     */
    protected string $method = 'resize';

    protected array $params = [];

    public function __construct(
        protected string $path,
        protected ?int $width = null,
        protected ?int $height = null,
    ) {
        parent::__construct();
    }

    /**
     * 保存图片
     */
    public function save(string $savePath, ?string $disk = null, array $options = []): ImageInterface
    {
        if ($disk) {
            $savePath = Storage::disk($disk)->path($savePath);
        }

        $image = $this->read($this->path);

        if ($this->saving instanceof Closure) {
            call_user_func_array($this->saving, [$this, $image]);
        }

        $method = $this->method;
        if ($this->isAllowOriginSize) {
            $method .= 'Down';
        }

        if (! method_exists($image, $method)) {
            throw new RuntimeException("不支持 {$method} 调整图片尺寸方法");
        }

        return $image->{$method}($this->width, $this->height, ...$this->params)->save($savePath, ...$options);
    }

    /**
     * 设置调整方法和参数
     *
     * @param  string  $method  方法名
     * @param  array  $params  参数
     * @return $this
     */
    protected function setMethod(string $method, array $params = []): static
    {
        $this->method = $method;
        $this->params = $params;

        return $this;
    }

    /**
     * saving
     *
     * @return $this
     */
    public function saving(Closure $closure): static
    {
        $this->saving = $closure;

        return $this;
    }

    /**
     * 不允许超过原始图片尺寸
     *
     * @return $this
     */
    public function disallowOriginSize(): static
    {
        $this->isAllowOriginSize = false;

        return $this;
    }

    /**
     * 等比例缩放图片
     *
     * @return $this
     */
    public function scale(): static
    {
        return $this->setMethod('scale');
    }

    /**
     * 等比例缩小图片（不会放大）
     *
     * @return $this
     */
    public function scaleDown(): static
    {
        return $this->setMethod('scaleDown');
    }

    /**
     * 覆盖模式调整图片尺寸，可能会裁剪部分图片
     *
     * @param  string  $position  位置，默认为center
     * @return $this
     */
    public function cover(string $position = 'center'): static
    {
        return $this->setMethod('cover', [$position]);
    }

    /**
     * 覆盖模式调整图片尺寸（不会放大），可能会裁剪部分图片
     *
     * @param  string  $position  位置，默认为center
     * @return $this
     */
    public function coverDown(string $position = 'center'): static
    {
        return $this->setMethod('coverDown', [$position]);
    }

    /**
     * 填充模式调整图片尺寸，会在图片周围添加背景色
     *
     * @param  mixed  $background  背景色，默认为ffffff（白色）
     * @param  string  $position  位置，默认为center
     * @return $this
     */
    public function pad(mixed $background = 'ffffff', string $position = 'center'): static
    {
        return $this->setMethod('pad', [$background, $position]);
    }

    /**
     * 包含模式调整图片尺寸，会保持图片比例并添加背景色
     *
     * @param  mixed  $background  背景色，默认为ffffff（白色）
     * @param  string  $position  位置，默认为center
     * @return $this
     */
    public function contain(mixed $background = 'ffffff', string $position = 'center'): static
    {
        return $this->setMethod('contain', [$background, $position]);
    }

    /**
     * 裁剪图片
     *
     * @param  int  $offset_x  X轴偏移，默认为0
     * @param  int  $offset_y  Y轴偏移，默认为0
     * @param  mixed  $background  背景色，默认为ffffff（白色）
     * @param  string  $position  位置，默认为top-left
     * @return $this
     */
    public function crop(int $offset_x = 0, int $offset_y = 0, mixed $background = 'ffffff', string $position = 'top-left'): static
    {
        return $this->setMethod('crop', [$offset_x, $offset_y, $background, $position]);
    }

    /**
     * 调整画布大小
     *
     * @param  mixed  $background  背景色，默认为ffffff（白色）
     * @param  string  $position  位置，默认为center
     * @return $this
     */
    public function resizeCanvas(mixed $background = 'ffffff', string $position = 'center'): static
    {
        return $this->setMethod('resizeCanvas', [$background, $position]);
    }

    /**
     * 相对调整画布大小
     *
     * @param  mixed  $background  背景色，默认为ffffff（白色）
     * @param  string  $position  位置，默认为center
     * @return $this
     */
    public function resizeCanvasRelative(mixed $background = 'ffffff', string $position = 'center'): static
    {
        return $this->setMethod('resizeCanvasRelative', [$background, $position]);
    }

    /**
     * 裁剪图片边缘
     *
     * @param  int  $tolerance  容差值，默认为0
     * @return $this
     */
    public function trim(int $tolerance = 0): static
    {
        return $this->setMethod('trim', [$tolerance]);
    }
}
