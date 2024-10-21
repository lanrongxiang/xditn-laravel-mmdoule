<?php

declare(strict_types=1);

namespace Xditn\Support;

use Illuminate\Filesystem\Filesystem;
use Exception;
use Xditn\MModule;

class Installer
{
    /**
     * 复制模块
     *
     * @param array $modules 模块列表
     *
     * @return void
     */
    public static function copyModules(array $modules): void
    {
        $filesystem = new Filesystem();
        foreach ($modules as $module) {
            $module = ucfirst($module);
            $source      = __DIR__ . '/../Base/modules/' . $module;
            $destination = MModule::getModulePath($module);
            try {
                if (!$filesystem->exists($source)) {
                    echo "源目录不存在: $source\n";
                    continue;
                }
                // 创建目标目录
                if (!$filesystem->exists($destination)) {
                    $filesystem->makeDirectory($destination, 0755, true);
                    echo "目标目录已创建: $destination\n";
                }
                // 递归复制目录和文件
                self::recursiveCopy($source, $destination);
                echo "模块 {$module} 已成功复制到 {$destination}\n";
            } catch (Exception $exception) {
                echo "复制过程中出现错误: " . $exception->getMessage() . "\n";
            }
        }
    }

    /**
     * 递归复制文件夹和文件
     *
     * @param string $source
     * @param string $destination
     *
     * @return void
     * @throws Exception 如果源目录不可读或无法创建目标目录时抛出异常
     */
    private static function recursiveCopy(string $source, string $destination): void
    {
        // 检查源是否是目录且可读
        if (!is_dir($source) || !is_readable($source)) {
            throw new Exception("源目录不可读或不存在: $source");
        }
        // 尝试创建目标目录
        if (!is_dir($destination) && !mkdir($destination, 0755, true) && !is_dir($destination)) {
            throw new Exception("无法创建目标目录: $destination");
        }
        // 打开源目录
        $dir = opendir($source);
        // 确保目录成功打开
        if ($dir === false) {
            throw new Exception("无法打开源目录: $source");
        }
        while (($file = readdir($dir)) !== false) {
            // 跳过当前目录和父目录
            if ($file === '.' || $file === '..') {
                continue;
            }
            $sourcePath      = $source . DIRECTORY_SEPARATOR . $file;
            $destinationPath = $destination . DIRECTORY_SEPARATOR . $file;
            // 如果是目录，递归复制
            if (is_dir($sourcePath)) {
                self::recursiveCopy($sourcePath, $destinationPath);
            } elseif (is_file($sourcePath) || is_link($sourcePath)) {
                // 如果是文件或符号链接，复制文件
                if (!copy($sourcePath, $destinationPath)) {
                    throw new Exception("无法复制文件: $sourcePath 到 $destinationPath");
                }
            }
        }
        closedir($dir);
    }
}
