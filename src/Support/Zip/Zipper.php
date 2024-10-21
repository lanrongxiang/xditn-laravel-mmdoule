<?php

declare(strict_types=1);

namespace Xditn\Support\Zip;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Zipper 类用于处理 zip 文件
 */
class Zipper
{
    public const int WHITELIST   = 1;  // 白名单模式
    public const int BLACKLIST   = 2;  // 黑名单模式
    public const int EXACT_MATCH = 4;  // 精确匹配模式

    private string         $currentFolder = '';    // 当前文件夹路径
    private Filesystem     $file;                  // 文件系统处理器
    private ?ZipRepository $repository    = null;  // zip 仓库处理器
    private string         $filePath;              // 当前 zip 文件路径

    /**
     * 构造函数
     *
     * @param Filesystem|null $fs
     */
    public function __construct(Filesystem $fs = null)
    {
        $this->file = $fs ?? new Filesystem();
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        $this->repository?->close();
    }

    /**
     * 创建或打开 zip 文件
     *
     * @param string $pathToFile zip 文件路径
     *
     * @return $this
     * @throws Exception
     */
    public function make(string $pathToFile): self
    {
        $new              = $this->createArchiveFile($pathToFile);
        $this->repository = new ZipRepository($pathToFile, $new);
        $this->filePath   = $pathToFile;
        return $this;
    }

    /**
     * 创建或打开 zip 文件
     *
     * @param string $pathToFile zip 文件路径
     *
     * @return $this
     * @throws Exception
     */
    public function zip(string $pathToFile): self
    {
        return $this->make($pathToFile);
    }

    /**
     * 创建或打开 phar 文件
     *
     * @param string $pathToFile phar 文件路径
     *
     * @return $this
     * @throws Exception
     */
    public function phar(string $pathToFile): self
    {
        return $this->make($pathToFile);
    }

    /**
     * 创建或打开 rar 文件
     *
     * @param string $pathToFile rar 文件路径
     *
     * @return $this
     * @throws Exception
     */
    public function rar(string $pathToFile): self
    {
        return $this->make($pathToFile);
    }

    /**
     * 将 zip 文件解压到指定路径
     *
     * @param string $path        解压目标路径
     * @param array  $files       文件数组
     * @param int    $methodFlags 解压模式（白名单、黑名单或精确匹配）
     *
     * @throws Exception
     */
    public function extractTo(string $path, array $files = [], int $methodFlags = self::BLACKLIST): void
    {
        if (!$this->file->exists($path) && !$this->file->makeDirectory($path, 0755, true)) {
            throw new RuntimeException('创建文件夹失败');
        }
        $matchingMethod = ($methodFlags & self::EXACT_MATCH) ? fn($haystack) => in_array($haystack, $files, true) : fn(
            $haystack
        ) => Str::startsWith($haystack, $files);
        $this->extractFilesInternal(
            $path,
            $methodFlags & self::WHITELIST ? $matchingMethod : fn($filename) => !$matchingMethod($filename)
        );
    }

    /**
     * 使用正则表达式提取文件
     *
     * @param string $extractToPath 解压路径
     * @param string $regex         正则表达式
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function extractMatchingRegex(string $extractToPath, string $regex): void
    {
        if (empty($regex)) {
            throw new InvalidArgumentException('缺少有效的正则表达式');
        }
        $this->extractFilesInternal($extractToPath, function ($filename) use ($regex)
        {
            if (($match = preg_match($regex, $filename)) === 1) {
                return true;
            }
            if ($match === false) {
                throw new RuntimeException("正则表达式匹配 '$filename' 失败，请检查正则表达式是否有效");
            }
            return false;
        });
    }

    /**
     * 获取 zip 文件中的单个文件内容
     *
     * @param string $filePath 文件路径
     *
     * @return string 文件内容
     * @throws Exception
     */
    public function getFileContent(string $filePath): string
    {
        if (!$this->repository->fileExists($filePath)) {
            throw new Exception(sprintf('文件 "%s" 不存在', $filePath));
        }
        return $this->repository->getFileContent($filePath);
    }

    /**
     * 添加文件或目录到 zip
     *
     * @param array|string $pathToAdd 添加的文件或目录路径
     * @param mixed|null   $fileName  文件名称（可选）
     *
     * @return $this
     */
    public function add(array|string $pathToAdd, mixed $fileName = null): self
    {
        if (is_array($pathToAdd)) {
            array_walk_recursive($pathToAdd, function ($path, $key) use ($fileName)
            {
                $this->add($path, is_int($key) ? null : $key);
            });
        } elseif ($this->file->isFile($pathToAdd)) {
            $this->addFile($pathToAdd, $fileName);
        } else {
            $this->addDir($pathToAdd);
        }
        return $this;
    }

    /**
     * 添加空目录到 zip
     *
     * @param string $dirName 目录名称
     *
     * @return $this
     */
    public function addEmptyDir(string $dirName): self
    {
        $this->repository->addEmptyDir($dirName);
        return $this;
    }

    /**
     * 将字符串内容添加为 zip 中的文件
     *
     * @param string $filename 文件名称
     * @param string $content  文件内容
     *
     * @return $this
     */
    public function addString(string $filename, string $content): self
    {
        $this->addFromString($filename, $content);
        return $this;
    }

    /**
     * 获取 zip 文件的状态
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->repository->getStatus();
    }

    /**
     * 从 zip 中移除文件或目录
     *
     * @param array|string $fileToRemove 要移除的文件或目录
     *
     * @return $this
     */
    public function remove(array|string $fileToRemove): self
    {
        if (is_array($fileToRemove)) {
            $this->repository->each(function ($file) use ($fileToRemove)
            {
                if (Str::startsWith($file, $fileToRemove)) {
                    $this->repository->removeFile($file);
                }
            });
        } else {
            $this->repository->removeFile($fileToRemove);
        }
        return $this;
    }

    /**
     * 获取当前 zip 文件的路径
     *
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * 设置 zip 文件的解压密码
     *
     * @param string $password 密码
     *
     * @return bool
     */
    public function usePassword(string $password): bool
    {
        return $this->repository->usePassword($password);
    }

    /**
     * 关闭 zip 文件
     */
    public function close(): void
    {
        $this->repository?->close();
        $this->filePath = '';
    }

    /**
     * 设置 zip 内部文件夹路径
     *
     * @param string $path 路径
     *
     * @return $this
     */
    public function folder(string $path): self
    {
        $this->currentFolder = $path;
        return $this;
    }

    /**
     * 重置为 zip 文件的根目录
     *
     * @return $this
     */
    public function home(): self
    {
        $this->currentFolder = '';
        return $this;
    }

    /**
     * 删除 zip 文件
     */
    public function delete(): void
    {
        $this->repository?->close();
        $this->file->delete($this->filePath);
        $this->filePath = '';
    }

    /**
     * 获取 zip 文件的类型
     *
     * @return string
     */
    public function getArchiveType(): string
    {
        return get_class($this->repository);
    }

    /**
     * 获取 zip 内部的文件夹路径
     *
     * @return string
     */
    public function getCurrentFolderPath(): string
    {
        return $this->currentFolder;
    }

    /**
     * 检查 zip 文件中是否包含指定文件
     *
     * @param string $fileInArchive 文件路径
     *
     * @return bool
     */
    public function contains(string $fileInArchive): bool
    {
        return $this->repository->fileExists($fileInArchive);
    }

    /**
     * 获取带有斜杠的当前文件夹路径
     *
     * @return string
     */
    private function getCurrentFolderWithTrailingSlash(): string
    {
        return rtrim($this->currentFolder, '/\\') . '/';
    }

    //---------------------PRIVATE FUNCTIONS-------------

    /**
     * 创建或打开 zip 文件
     *
     * @param string $pathToZip 文件路径
     *
     * @return bool
     * @throws Exception
     */
    private function createArchiveFile(string $pathToZip): bool
    {
        if (!$this->file->exists($pathToZip)) {
            $dirname = dirname($pathToZip);
            if (!$this->file->exists($dirname) && !$this->file->makeDirectory($dirname, 0755, true)) {
                throw new RuntimeException('创建文件夹失败');
            } elseif (!$this->file->isWritable($dirname)) {
                throw new Exception(sprintf('路径 "%s" 不可写', $pathToZip));
            }
            return true;
        }
        return false;
    }

    /**
     * 添加目录到 zip
     *
     * @param string $pathToDir 目录路径
     */
    private function addDir(string $pathToDir): void
    {
        foreach ($this->file->files($pathToDir) as $file) {
            $this->addFile($pathToDir . '/' . basename($file));
        }
        foreach ($this->file->directories($pathToDir) as $dir) {
            $oldFolder           = $this->currentFolder;
            $this->currentFolder = $this->getCurrentFolderWithTrailingSlash() . basename($dir);
            $this->addDir($dir);
            $this->currentFolder = $oldFolder;
        }
    }

    /**
     * 添加文件到 zip
     *
     * @param string      $pathToAdd 文件路径
     * @param string|null $fileName  文件名
     */
    private function addFile(string $pathToAdd, string $fileName = null): void
    {
        $fileName = $fileName ?? basename($pathToAdd);
        $this->repository->addFile($pathToAdd, $this->getInternalPath() . $fileName);
    }

    /**
     * 从内容添加文件到 zip
     *
     * @param string $filename 文件名
     * @param string $content  文件内容
     */
    private function addFromString(string $filename, string $content): void
    {
        $this->repository->addFromString($this->getInternalPath() . $filename, $content);
    }

    /**
     * 内部提取文件
     *
     * @param string   $path           提取路径
     * @param callable $matchingMethod 匹配方法
     */
    private function extractFilesInternal(string $path, callable $matchingMethod): void
    {
        $self = $this;
        $this->repository->each(function ($fileName) use ($path, $matchingMethod, $self)
        {
            if (!Str::startsWith($fileName, $self->getCurrentFolderWithTrailingSlash())) {
                return;
            }
            $filename = str_replace($self->getInternalPath(), '', $fileName);
            if ($matchingMethod($filename)) {
                $self->extractOneFileInternal($fileName, $path);
            }
        });
    }

    /**
     * 内部提取单个文件
     *
     * @param string $fileName 文件名
     * @param string $path     提取路径
     *
     * @throws RuntimeException
     */
    private function extractOneFileInternal(string $fileName, string $path): void
    {
        $tmpPath = str_replace($this->getInternalPath(), '', $fileName);
        if (str_contains($fileName, '../') || str_contains($fileName, '..\\')) {
            throw new RuntimeException('文件路径中包含非法字符');
        }
        $dir = pathinfo($path . DIRECTORY_SEPARATOR . $tmpPath, PATHINFO_DIRNAME);
        if (!$this->file->exists($dir) && !$this->file->makeDirectory($dir, 0755, true, true)) {
            throw new RuntimeException('创建目录失败');
        }
        $toPath     = $path . DIRECTORY_SEPARATOR . $tmpPath;
        $fileStream = $this->getRepository()->getFileStream($fileName);
        $this->getFileHandler()->put($toPath, $fileStream);
    }

    /**
     * 获取 zip 文件的内部路径
     *
     * @return string
     */
    private function getInternalPath(): string
    {
        return empty($this->currentFolder) ? '' : $this->currentFolder . '/';
    }

    /**
     * 获取 zip 仓库处理器
     *
     * @return ZipRepository
     */
    public function getRepository(): ZipRepository
    {
        return $this->repository;
    }

    /**
     * 获取文件处理器
     *
     * @return Filesystem
     */
    public function getFileHandler(): Filesystem
    {
        return $this->file;
    }
}
