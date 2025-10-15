<?php

namespace Xditn\Support\Image;

use Closure;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Interfaces\ImageInterface;
use InvalidArgumentException;

class Watermark extends AbstractImage
{
    protected string $position = 'top-left';

    protected int $offsetX = 0;

    protected int $offsetY = 0;

    /**
     * 透明度
     */
    protected int $opacity = 100;

    protected ?Closure $saving = null;

    public function __construct(
        protected string $path,

        protected string $watermarkPath
    ) {
        parent::__construct();
    }

    /**
     * @return $this
     */
    public function offset(int $x, int $y): static
    {
        $this->offsetX = $x;
        $this->offsetY = $y;

        return $this;
    }

    public function save(string $path, ?string $disk = null, array $options = []): ImageInterface
    {
        if ($disk) {
            $path = Storage::disk($disk)->path($path);
        }

        $image = $this->read($this->path);

        if ($this->saving instanceof Closure) {
            call_user_func_array($this->saving, [$this, $image]);
        }

        return $image
            ->place(
                $this->watermarkPath,
                $this->position,
                $this->offsetX,
                $this->offsetY,
                $this->opacity
            )
            ->save($path, ...$options);
    }

    /**
     * @return $this
     */
    public function opacity(int $opacity): static
    {
        if ($opacity < 0 || $opacity > 100) {
            throw new InvalidArgumentException('透明度范围为 0-100');
        }

        $this->opacity = $opacity;

        return $this;
    }

    /**
     * @return $this
     */
    public function bottom(): static
    {
        return $this->position('bottom');
    }

    public function bottomLeft(): static
    {
        return $this->position('bottom-left');
    }

    public function bottomRight(): static
    {
        return $this->position('bottom-right');
    }

    public function center(): static
    {
        return $this->position('center');
    }

    public function top(): static
    {
        return $this->position('top');
    }

    public function topLeft(): static
    {
        return $this->position('top-left');
    }

    public function topRight(): static
    {
        return $this->position('top-right');
    }

    public function right(): static
    {
        return $this->position('right');
    }

    public function left(): static
    {
        return $this->position('left');
    }

    protected function position(string $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function saving(Closure $saving): static
    {
        $this->saving = $saving;

        return $this;
    }
}
