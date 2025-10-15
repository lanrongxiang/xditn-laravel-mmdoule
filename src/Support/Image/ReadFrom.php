<?php

namespace Xditn\Support\Image;

use Illuminate\Support\Facades\Storage;

trait ReadFrom
{
    /**
     * 默认处理本地磁盘的 uploads 文件
     */
    protected ?string $disk = null;

    /**
     * 获取实际路径
     */
    public function getRealPath(string $path): string
    {
        if (file_exists($path)) {
            return $path;
        }

        if ($this->getDisk()) {
            $path = $this->getImageFromDisk($path);
        }

        if (! file_exists($path)) {
            throw new \RuntimeException('图片不存在');
        }

        return $path;
    }

    /**
     * 从磁盘获取图片路径
     */
    protected function getImageFromDisk(string $path): string
    {
        return Storage::disk($this->getDisk())->path($path);
    }

    /**
     * 设置磁盘
     *
     * @return $this
     */
    public function disk(?string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * 默认读取磁盘
     */
    public function getDisk(): mixed
    {
        if ($this->disk) {
            return $this->disk;
        }

        return config('xditn.image.read_from', 'uploads');
    }
}
