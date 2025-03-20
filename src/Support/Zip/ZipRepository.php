<?php

namespace Xditn\Support\Zip;

use Exception;
use ZipArchive;

/**
 * ZipRepository 类用于封装 ZipArchive 操作，提供对 ZIP 文件的创建和管理功能。
 */
class ZipRepository
{
    private mixed $archive;

    /**
     * 构造函数，接收文件路径并根据需要创建 ZIP 文件。
     *
     * @param  string  $filePath ZIP 文件路径
     * @param  bool  $create   是否创建新文件
     * @param  ZipArchive|null  $archive  ZipArchive 对象
     *
     * @throws Exception
     */
    public function __construct(string $filePath, bool $create, ?ZipArchive $archive = null)
    {
        // 检查 ZipArchive 类是否可用
        if (! class_exists('ZipArchive')) {
            throw new Exception('错误：您的 PHP 版本未编译 zip 支持');
        }
        // 如果没有提供 ZipArchive 实例，则创建一个新的
        $this->archive = $archive ? $archive : new ZipArchive();
        // 尝试打开 ZIP 文件
        $res = $this->archive->open($filePath, ($create ? ZipArchive::CREATE : 0));
        if ($res !== true) {
            throw new Exception("错误：无法打开 $filePath ！错误信息：".$this->getErrorMessage($res));
        }
    }

    /**
     * 向打开的 ZIP 文件中添加文件。
     *
     * @param  string  $pathToFile    要添加的文件路径
     * @param  string  $pathInArchive 文件在 ZIP 中的路径
     */
    public function addFile(string $pathToFile, string $pathInArchive): void
    {
        $this->archive->addFile($pathToFile, $pathInArchive);
    }

    /**
     * 向 ZIP 文件中添加空目录。
     *
     * @param  string  $dirName 目录名称
     */
    public function addEmptyDir(string $dirName): void
    {
        $this->archive->addEmptyDir($dirName);
    }

    /**
     * 从内容中向 ZIP 文件中添加文件。
     *
     * @param  string  $name    文件名称
     * @param  string  $content 文件内容
     */
    public function addFromString(string $name, string $content): void
    {
        $this->archive->addFromString($name, $content);
    }

    /**
     * 从 ZIP 文件中永久删除文件。
     *
     * @param  string  $pathInArchive 要删除的文件路径
     */
    public function removeFile(string $pathInArchive): void
    {
        $this->archive->deleteName($pathInArchive);
    }

    /**
     * 获取 ZIP 文件中的文件内容。
     *
     * @param  string  $pathInArchive 文件路径
     * @return string 文件内容
     */
    public function getFileContent(string $pathInArchive): string
    {
        return $this->archive->getFromName($pathInArchive);
    }

    /**
     * 获取 ZIP 文件中某个文件的流。
     *
     * @param  string  $pathInArchive 文件路径
     * @return bool
     */
    public function getFileStream(string $pathInArchive): bool
    {
        return $this->archive->getStream($pathInArchive);
    }

    /**
     * 遍历 ZIP 文件中的每个条目并执行回调。
     *
     * @param  callable  $callback 回调函数
     */
    public function each(callable $callback): void
    {
        for ($i = 0; $i < $this->archive->numFiles; $i++) {
            // 跳过文件夹
            $stats = $this->archive->statIndex($i);
            if ($stats['size'] === 0 && $stats['crc'] === 0) {
                continue;
            }
            call_user_func($callback, $this->archive->getNameIndex($i), $stats);
        }
    }

    /**
     * 检查文件是否存在于 ZIP 文件中。
     *
     * @param  string  $fileInArchive 文件路径
     * @return bool
     */
    public function fileExists(string $fileInArchive): bool
    {
        return $this->archive->locateName($fileInArchive) !== false;
    }

    /**
     * 设置用于解压缩的密码。
     *
     * @param  string  $password 密码
     * @return bool
     */
    public function usePassword(string $password): bool
    {
        return $this->archive->setPassword($password);
    }

    /**
     * 获取 ZIP 文件的状态。
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->archive->getStatusString();
    }

    /**
     * 获取 ZIPArchive 实例。
     *
     * @return mixed|ZipArchive
     */
    public function getArchive(): mixed
    {
        return $this->archive;
    }

    /**
     * 关闭 ZIP 文件并保存。
     */
    public function close(): void
    {
        @$this->archive->close();
    }

    /**
     * 获取错误信息。
     *
     * @param  int  $resultCode 错误代码
     * @return string
     */
    private function getErrorMessage(int $resultCode): string
    {
        return match ($resultCode) {
            ZipArchive::ER_EXISTS => 'ZipArchive::ER_EXISTS - 文件已存在。',
            ZipArchive::ER_INCONS => 'ZipArchive::ER_INCONS - ZIP 文件不一致。',
            ZipArchive::ER_MEMORY => 'ZipArchive::ER_MEMORY - 内存分配失败。',
            ZipArchive::ER_NOENT => 'ZipArchive::ER_NOENT - 没有这样的文件。',
            ZipArchive::ER_NOZIP => 'ZipArchive::ER_NOZIP - 不是 ZIP 文件。',
            ZipArchive::ER_OPEN => 'ZipArchive::ER_OPEN - 无法打开文件。',
            ZipArchive::ER_READ => 'ZipArchive::ER_READ - 读取错误。',
            ZipArchive::ER_SEEK => 'ZipArchive::ER_SEEK - 寻址错误。',
            default => "发生未知错误 [$resultCode]。",
        };
    }
}
