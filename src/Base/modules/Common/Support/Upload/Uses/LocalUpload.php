<?php

namespace Xditn\Base\modules\Common\Support\Upload\Uses;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * local upload
 */
class LocalUpload extends Upload
{
    /**
     * upload
     */
    public function upload(): array
    {
        $info = $this->addUrl($this->getUploadPath());
        $info['driver'] = 'local';

        return $info;
    }

    /**
     * app url
     */
    protected function addUrl(array $path): mixed
    {
        $appUrl = config('app.url');

        $path['path'] = Str::of($path['path'])->replace('\\', '/')->prepend('/')->toString();

        if ($appUrl) {
          //  $path['url'] = $appUrl.'/'.$path['path'];
        }

        return $path;
    }

    /**
     * local upload
     */
    protected function localUpload(): string
    {
        $this->checkSize();

        $uploadPath = 'uploads'.DIRECTORY_SEPARATOR.
            'attachments'.DIRECTORY_SEPARATOR.
            date('Y-m-d');

        $storePath = storage_path($uploadPath);

        $filename = $this->generateImageName($this->getUploadedFileExt());

        Storage::build([
            'driver' => 'local',
            'root' => $storePath,
        ])->put($filename, $this->file->getContent());

        return $uploadPath.DIRECTORY_SEPARATOR.$filename;
    }
}
