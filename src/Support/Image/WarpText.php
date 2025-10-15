<?php

namespace Xditn\Support\Image;

use Closure;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Typography\FontFactory;

/**
 * 添加字体
 */
class WarpText extends AbstractImage
{
    /**
     * 字体大小
     */
    protected ?float $fontsize = null;

    /**
     * 字体文件
     */
    protected ?string $fontFile = null;

    /**
     * 颜色
     */
    protected mixed $color = null;

    /**
     * 文本描边（轮廓）
     * 为要写入的文本添加所需颜色的轮廓效果。您还可以确定文本字符笔画的宽度。
     */
    protected ?string $stroke = null;

    protected int $strokeWidth = 1;

    /**
     * 水平对齐
     * 定义文本从基点开始的水平对齐方式。可能的值包括左对齐、右对齐和居中对齐。默认值：left, right, center
     */
    protected string $align = 'left';

    /**
     * 垂直对齐
     * 定义文本从基点开始的垂直对齐方式。可能的值包括顶部、底部和中间。默认值：bottom, top, middle
     */
    protected string $valign = 'bottom';

    /**
     * 定义文本块的行高。仅适用于多行文本。默认值为1.25。
     */
    protected float $lineHeight = 1.25;

    /**
     * 将文本块顺时针旋转至所需角度。
     */
    protected ?float $angle = null;

    /**
     * X轴偏移
     */
    protected int $offsetX = 0;

    /**
     * Y轴偏移
     */
    protected int $offsetY = 0;

    protected bool $center = false;

    protected bool $middle = false;

    protected ?Closure $saving = null;

    /**
     * 文本换行
     * 指定文本的最大宽度（以像素为单位）。要渲染的文本将使用指定的字体选项进行解析，并且每行都会自动换行到最大宽度。不执行连字符连接。
     */
    protected ?int $wrap = null;

    public function __construct(
        protected string $path,
        protected string $text
    ) {
        parent::__construct();
    }

    /**
     * XY轴偏移
     *
     * @return WarpText
     */
    public function offset(int $x = 0, int $y = 0): static
    {
        $this->offsetX = $x;
        $this->offsetY = $y;

        return $this;
    }

    /**
     * 保存
     */
    public function save(string $path, ?string $disk = null, array $options = []): ImageInterface
    {
        $image = $this->read($this->path);

        if ($disk) {
            $path = Storage::disk($disk)->path($path);
        }

        if ($this->center) {
            $this->offsetX = $image->width() / 2;
        }

        if ($this->middle) {
            $this->offsetY = $image->height() / 2;
        }

        // 如果没有设置 offsetY，为了防止用户看不到，默认使用字体的高度
        if (! $this->offsetY) {
            $this->offsetY = $this->fontsize;
        }

        if ($this->saving instanceof Closure) {
            call_user_func_array($this->saving, [$this, $image]);
        }

        return $image->text($this->text, $this->offsetX, $this->offsetY, function (FontFactory $font) {
            if ($this->fontFile) {
                $font->filename($this->fontFile);
            }

            if ($this->fontsize) {
                $font->size($this->fontsize);
            }

            if ($this->color) {
                $font->color($this->color);
            }

            if ($this->stroke) {
                $font->stroke($this->stroke, $this->strokeWidth);
            }

            if ($this->align) {
                $font->align($this->align);
            }

            if ($this->valign) {
                $font->valign($this->valign);
            }

            if ($this->lineHeight) {
                $font->lineHeight($this->lineHeight);
            }

            if ($this->angle) {
                $font->angle($this->angle);
            }

            if ($this->wrap) {
                $font->wrap($this->wrap);
            }
        })->save($path, ...$options);
    }

    /**
     * 设置字体大小
     *
     * @param  float|null  $size  字体大小
     * @return $this
     */
    public function fontsize(?float $size): static
    {
        $this->fontsize = $size;

        return $this;
    }

    /**
     * 设置字体文件
     *
     * @param  string|null  $ttfFile  字体文件地址
     * @return $this
     */
    public function ttf(?string $ttfFile): static
    {
        $this->fontFile = $ttfFile;

        return $this;
    }

    /**
     * 设置文本颜色
     *
     * @param  mixed  $color  颜色值
     * @return $this
     */
    public function color(mixed $color): static
    {
        $this->color = $color;

        return $this;
    }

    /**
     * 设置文本描边（轮廓）
     *
     * @param  string|null  $stroke  描边值
     * @return $this
     */
    public function stroke(?string $stroke, int $width = 1): static
    {
        $this->stroke = $stroke;

        $this->strokeWidth = $width;

        return $this;
    }

    /**
     * 设置水平对齐方式
     *
     * @param  string  $align  水平对齐方式 (left, right, center)
     * @return $this
     */
    protected function align(string $align): static
    {
        $this->align = $align;

        return $this;
    }

    /**
     * 设置垂直对齐方式
     *
     * @param  string  $valign  垂直对齐方式 (bottom, top, middle)
     * @return $this
     */
    protected function valign(string $valign): static
    {
        $this->valign = $valign;

        return $this;
    }

    /**
     * 设置行高
     *
     * @param  float  $lineHeight  行高值
     * @return $this
     */
    public function lineHeight(float $lineHeight): static
    {
        $this->lineHeight = $lineHeight;

        return $this;
    }

    /**
     * 设置文本旋转角度
     *
     * @param  float|null  $angle  旋转角度
     * @return $this
     */
    public function angle(?float $angle): static
    {
        $this->angle = $angle;

        return $this;
    }

    /**
     * 设置文本换行宽度
     *
     * @param  int|null  $wrap  最大宽度（像素）
     * @return $this
     */
    public function wrap(?int $wrap): static
    {
        $this->wrap = $wrap;

        return $this;
    }

    public function alignLeft(): static
    {
        $this->align = 'left';

        return $this;
    }

    public function alignRight(): static
    {
        $this->align = 'right';

        return $this;
    }

    public function alignCenter(): static
    {
        $this->align = 'center';

        return $this;
    }

    public function valignTop(): static
    {
        $this->valign = 'top';

        return $this;
    }

    public function valignBottom(): static
    {
        $this->valign = 'bottom';

        return $this;
    }

    public function valignMiddle(): static
    {
        $this->valign = 'middle';

        return $this;
    }

    public function center(): static
    {
        $this->center = true;

        return $this;
    }

    public function middle(): static
    {
        $this->middle = true;

        return $this;
    }

    /**
     * @param Closure $callback
     * @return $this
     */
    public function saving(Closure $callback): static
    {
        $this->saving = $callback;

        return $this;
    }
}
