<?php

declare(strict_types=1);

namespace Xditn\Support\Zip;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Zipper 类是对 ZipArchive 方法的封装，提供了更多便捷的压缩和解压操作
 */
class Zipper
{
    public const WHITELIST = 1;   // 白名单模式

    public const BLACKLIST = 2;   // 黑名单模式

    public const EXACT_MATCH = 4;   // 精确匹配模式

    private string         $currentFolder = '';   // 当前压缩包内文件夹

    private Filesystem     $file;                 // 文件系统对象

    private ?ZipRepository $repository = null; // 压缩包操作对象

    private string         $filePath;             // 当前压缩包路径

    /**
     * 构造函数，初始化文件系统
     *
     * @param  Filesystem|null  $fs 文件系统实例
     */
    public function __construct(Filesystem $fs = null)
    {
        $this->file = $fs ?: new Filesystem();
    }

    /**
     * 析构函数，自动关闭打开的压缩包
     */
    public function __destruct()
    {
        $this->repository?->close();
    }

    /**
     * 创建或打开一个 zip 压缩文件
     *
     * @param  string  $pathToFile 压缩文件路径
     * @return $this
     *
     * @throws Exception
     */
    public function make(string $pathToFile): Zipper
    {
        $new = $this->createArchiveFile($pathToFile);
        $this->repository = new ZipRepository($pathToFile, $new);
        $this->filePath = $pathToFile;

        return $this;
    }

    /**
     * 打开或创建 zip 文件
     *
     * @param  string  $pathToFile 文件路径
     * @return Zipper
     *
     * @throws Exception
     */
    public function zip(string $pathToFile): Zipper
    {
        return $this->make($pathToFile);
    }

    /**
     * 打开或创建 phar 文件
     *
     * @param  string  $pathToFile 文件路径
     * @return Zipper
     *
     * @throws Exception
     */
    public function phar(string $pathToFile): Zipper
    {
        return $this->make($pathToFile);
    }

    /**
     * 打开或创建 rar 文件
     *
     * @param  string  $pathToFile 文件路径
     * @return Zipper
     *
     * @throws Exception
     */
    public function rar(string $pathToFile): Zipper
    {
        return $this->make($pathToFile);
    }

    /**
     * 将压缩包内容解压到指定路径
     *
     * @param  string  $path        解压路径
     * @param  array  $files       需要解压的文件列表（可选）
     * @param  int  $methodFlags 解压方法标记（黑名单或白名单）
     *
     * @throws Exception
     */
    public function extractTo(string $path, array $files = [], int $methodFlags = self::BLACKLIST): void
    {
        if (! $this->file->exists($path) && ! $this->file->makeDirectory($path, 0755, true)) {
            throw new RuntimeException('无法创建文件夹');
        }
        // 设置匹配方法
        $matchingMethod = $methodFlags & self::EXACT_MATCH ? fn ($haystack) => in_array($haystack, $files, true) : fn (
            $haystack
        ) => Str::startsWith($haystack, $files);
        // 解压文件：根据白名单或黑名单模式处理
        if ($methodFlags & self::WHITELIST) {
            $this->extractFilesInternal($path, $matchingMethod);
        } else {
            $this->extractFilesInternal($path, fn ($filename) => ! $matchingMethod($filename));
        }
    }

    /**
     * 使用正则表达式解压符合条件的文件
     *
     * @param  string  $extractToPath 解压路径
     * @param  string  $regex         正则表达式
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function extractMatchingRegex(string $extractToPath, string $regex): void
    {
        if (empty($regex)) {
            throw new InvalidArgumentException('缺少有效的正则表达式参数');
        }
        $this->extractFilesInternal($extractToPath, function ($filename) use ($regex) {
            if (preg_match($regex, $filename) === 1) {
                return true;
            }
            if (preg_last_error() !== PREG_NO_ERROR) {
                throw new RuntimeException("正则表达式错误: $regex");
            }

            return false;
        });
    }

    /**
     * 获取压缩包中某个文件的内容
     *
     * @param  string  $filePath 文件路径
     * @return string 文件内容
     *
     * @throws Exception
     */
    public function getFileContent(string $filePath): string
    {
        if (! $this->repository->fileExists($filePath)) {
            throw new Exception("文件 '$filePath' 不存在");
        }

        return $this->repository->getFileContent($filePath);
    }

    /**
     * 向压缩包添加文件或文件夹
     *
     * @param  array|string  $pathToAdd 文件或文件夹路径
     * @param  mixed|null  $fileName  重命名文件名称（可选）
     * @return $this
     */
    public function add(array|string $pathToAdd, mixed $fileName = null): Zipper
    {
        if (is_array($pathToAdd)) {
            foreach ($pathToAdd as $key => $dir) {
                $this->add(is_int($key) ? $dir : $dir, $key);
            }
        } elseif ($this->file->isFile($pathToAdd)) {
            $this->addFile($pathToAdd, $fileName ?? null);
        } else {
            $this->addDir($pathToAdd);
        }

        return $this;
    }

    /**
     * 向压缩包添加空目录
     *
     * @param  string  $dirName 目录名称
     * @return Zipper
     */
    public function addEmptyDir(string $dirName): Zipper
    {
        $this->repository->addEmptyDir($dirName);

        return $this;
    }

    /**
     * 将字符串内容添加为压缩包中的文件
     *
     * @param  string  $filename 文件名称
     * @param  string  $content  文件内容
     * @return $this
     */
    public function addString(string $filename, string $content): Zipper
    {
        $this->addFromString($filename, $content);

        return $this;
    }

    /**
     * 获取压缩包的状态
     *
     * @return string 压缩包状态
     */
    public function getStatus(): string
    {
        return $this->repository->getStatus();
    }

    /**
     * 从压缩包中移除文件或文件夹
     *
     * @param  array|string  $fileToRemove 文件或文件夹路径
     * @return $this
     */
    public function remove(array|string $fileToRemove): Zipper
    {
        if (is_array($fileToRemove)) {
            $this->repository->each(
                fn ($file) => Str::startsWith($file, $fileToRemove) && $this->repository->removeFile($file)
            );
        } else {
            $this->repository->removeFile($fileToRemove);
        }

        return $this;
    }

    /**
     * 获取当前压缩包的路径
     *
     * @return string 压缩包路径
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * 使用密码进行压缩包操作
     *
     * @param  string  $password 密码
     * @return bool
     */
    public function usePassword(string $password): bool
    {
        return $this->repository->usePassword($password);
    }

    /**
     * 关闭当前压缩包
     */
    public function close(): void
    {
        $this->repository?->close();
        $this->filePath = '';
    }

    /**
     * 设置当前内部目录，用于指定操作某一目录中的文件
     *
     * @param  string  $path 内部目录路径
     * @return $this
     */
    public function folder(string $path): Zipper
    {
        $this->currentFolder = $path;

        return $this;
    }

    /**
     * 将当前目录重置为根目录
     *
     * @return $this
     */
    public function home(): Zipper
    {
        $this->currentFolder = '';

        return $this;
    }

    /**
     * 删除压缩包文件
     */
    public function delete(): void
    {
        $this->repository?->close();
        $this->file->delete($this->filePath);
        $this->filePath = '';
    }

    /**
     * 获取压缩包类型
     *
     * @return string 压缩包类型
     */
    public function getArchiveType(): string
    {
        return get_class($this->repository);
    }

    /**
     * 获取当前压缩包内部文件夹路径
     *
     * @return string 当前内部文件夹路径
     */
    public function getCurrentFolderPath(): string
    {
        return $this->currentFolder;
    }

    /**
     * 检查压缩包中是否存在指定文件
     *
     * @param  string  $fileInArchive 文件路径
     * @return bool 是否存在
     */
    public function contains(string $fileInArchive): bool
    {
        return $this->repository->fileExists($fileInArchive);
    }

    /**
     * 获取 ZipRepository 对象
     *
     * @return ZipRepository
     */
    public function getRepository(): ZipRepository
    {
        return $this->repository;
    }

    /**
     * 获取文件系统处理器
     *
     * @return Filesystem
     */
    public function getFileHandler(): Filesystem
    {
        return $this->file;
    }

    /**
     * 获取当前内部文件夹路径
     *
     * @return string
     */
    public function getInternalPath(): string
    {
        return empty($this->currentFolder) ? '' : $this->currentFolder.'/';
    }

    /**
     * 列出压缩包中的所有文件
     *
     * @param  string|null  $regexFilter 正则表达式用于过滤文件
     * @return array 文件列表
     */
    public function listFiles(string $regexFilter = null): array
    {
        $filesList = [];
        $filter = $regexFilter ? function ($file) use (&$filesList, $regexFilter) {
            if (preg_match($regexFilter, $file)) {
                $filesList[] = $file;
            }
        } : fn ($file) => $filesList[] = $file;
        $this->repository->each($filter);

        return $filesList;
    }

    /**
     * 获取当前内部文件夹路径并加上斜杠
     *
     * @return string 带斜杠的路径
     */
    private function getCurrentFolderWithTrailingSlash(): string
    {
        return empty($this->currentFolder) || str_ends_with(
            $this->currentFolder,
            '/'
        ) ? $this->currentFolder : $this->currentFolder.'/';
    }

    /**
     * 创建压缩文件
     *
     * @param  string  $pathToZip 压缩文件路径
     * @return bool
     *
     * @throws Exception
     */
    private function createArchiveFile(string $pathToZip): bool
    {
        if (! $this->file->exists($pathToZip)) {
            $dirname = dirname($pathToZip);
            if (! $this->file->exists($dirname) && ! $this->file->makeDirectory($dirname, 0755, true)) {
                throw new RuntimeException('无法创建文件夹');
            }
            if (! $this->file->isWritable($dirname)) {
                throw new Exception(sprintf('路径 "%s" 不可写', $pathToZip));
            }

            return true;
        }

        return false;
    }

    /**
     * 递归添加文件夹到压缩包
     *
     * @param  string  $pathToDir 文件夹路径
     */
    private function addDir(string $pathToDir): void
    {
        foreach ($this->file->files($pathToDir) as $file) {
            $this->addFile($pathToDir.'/'.basename($file));
        }
        foreach ($this->file->directories($pathToDir) as $dir) {
            $oldFolder = $this->currentFolder;
            $this->currentFolder = empty($this->currentFolder) ? basename($dir) : $this->currentFolder.'/'.basename(
                    $dir
                );
            $this->addDir($pathToDir.'/'.basename($dir));
            $this->currentFolder = $oldFolder;
        }
    }

    /**
     * 添加文件到压缩包
     *
     * @param  string  $pathToAdd 文件路径
     * @param  string|null  $fileName  文件名称（可选）
     */
    private function addFile(string $pathToAdd, string $fileName = null): void
    {
        $fileName = $fileName ?: basename($pathToAdd);
        $this->repository->addFile($pathToAdd, $this->getInternalPath().$fileName);
    }

    /**
     * 添加字符串内容到压缩包
     *
     * @param  string  $filename 文件名
     * @param  string  $content  文件内容
     */
    private function addFromString(string $filename, string $content): void
    {
        $this->repository->addFromString($this->getInternalPath().$filename, $content);
    }

    /**
     * 内部文件解压
     *
     * @param  string  $path           解压路径
     * @param  callable  $matchingMethod 匹配方法
     */
    private function extractFilesInternal(string $path, callable $matchingMethod): void
    {
        $this->repository->each(function ($fileName) use ($path, $matchingMethod) {
            $currentPath = $this->getCurrentFolderWithTrailingSlash();
            if (! empty($currentPath) && ! Str::startsWith($fileName, $currentPath)) {
                return;
            }
            $filename = str_replace($this->getInternalPath(), '', $fileName);
            if ($matchingMethod($filename)) {
                $this->extractOneFileInternal($fileName, $path);
            }
        });
    }

    /**
     * 解压单个文件
     *
     * @param  string  $fileName 文件名
     * @param  string  $path     解压路径
     *
     * @throws RuntimeException
     */
    private function extractOneFileInternal(string $fileName, string $path): void
    {
        $tmpPath = str_replace($this->getInternalPath(), '', $fileName);
        if (str_contains($fileName, '../') || str_contains($fileName, '..\\')) {
            throw new RuntimeException('文件名中存在非法字符');
        }
        $dir = pathinfo($path.DIRECTORY_SEPARATOR.$tmpPath, PATHINFO_DIRNAME);
        if (! $this->file->exists($dir) && ! $this->file->makeDirectory($dir, 0755, true, true)) {
            throw new RuntimeException('无法创建文件夹');
        }
        $toPath = $path.DIRECTORY_SEPARATOR.$tmpPath;
        $fileStream = $this->getRepository()->getFileStream($fileName);
        $this->getFileHandler()->put($toPath, $fileStream);
    }
}
