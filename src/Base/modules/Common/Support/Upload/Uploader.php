<?php

namespace Xditn\Base\modules\Common\Support\Upload;

use Illuminate\Http\UploadedFile;
use Xditn\Base\modules\Common\Events\UploadedEvent;
use Xditn\Base\modules\Common\Support\Upload\Uses\LocalUpload;
use Xditn\Exceptions\FailedException;

class Uploader
{
    protected string $driver = 'local';

    protected int $categoryId = 0;

    /**
     * path
     */
    protected string $path = '';

    /**
     * upload
     */
    public function upload(UploadedFile $file): array
    {
        try {
            $uploadInfo = $this->getDriver()->setUploadedFile($file)->upload();
            // 附件分类ID
            $uploadInfo['category_id'] = $this->categoryId;
            UploadedEvent::dispatch($uploadInfo);

            return $uploadInfo;
        } catch (\Exception $exception) {
            throw new FailedException($exception->getMessage());
        }
    }

    /**
     *  get driver
     */
    public function getDriver()
    {
        $drivers = $this->getDrivers();

        $driver = $drivers[$this->driver] ?? null;

        if (! $driver) {
            throw new FailedException('Upload Driver Not Found');
        }

        return app($driver);
    }

    /**
     * set driver
     *
     * @return $this
     */
    public function setDriver(string $driver): static
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * @param  mixed  $categoryId
     * @return $this
     */
    public function withCategoryId(mixed $categoryId): static
    {
        $this->categoryId = intval($categoryId);

        return $this;
    }

    /**
     * get drivers
     *
     * @return string[]
     */
    public function getDrivers(): array
    {
        return [
            'local' => LocalUpload::class,
        ];
    }
}
