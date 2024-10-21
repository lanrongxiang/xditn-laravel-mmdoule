<?php

namespace Xditn\Support;

use Illuminate\Support\Composer as LaravelComposer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException;
use Xditn\Exceptions\FailedException;

/**
 * 扩展 Composer 类，处理包管理相关操作
 */
class Composer extends LaravelComposer
{
    // 是否忽略平台需求
    protected bool $ignorePlatformReqs = false;

    /**
     * 要求安装某个包
     *
     * @param  string  $package 包名
     * @return string 命令输出
     *
     * @throws PhpVersionNotSupportedException 如果 PHP 版本不支持
     */
    public function require(string $package): string
    {
        $this->checkPHPVersion();

        return $this->executeCommand(['require', $package]);
    }

    /**
     * 要求安装某个开发包
     *
     * @param  string  $package 包名
     * @return string 命令输出
     *
     * @throws PhpVersionNotSupportedException 如果 PHP 版本不支持
     */
    public function requireDev(string $package): string
    {
        $this->checkPHPVersion();

        return $this->executeCommand(['require', '--dev', $package]);
    }

    /**
     * 移除某个包
     *
     * @param  string  $package 包名
     */
    public function remove(string $package): void
    {
        $this->executeCommand(['remove', $package]);
    }

    /**
     * 执行 Composer 命令
     *
     * @param  array  $command 命令数组
     * @return string 命令输出
     *
     * @throws FailedException 如果命令执行失败
     */
    protected function executeCommand(array $command): string
    {
        $command = array_merge($this->findComposer(), $command);

        if ($this->ignorePlatformReqs) {
            $command[] = '--ignore-platform-reqs';
        }

        $process = $this->getProcess($command);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new FailedException($process->getErrorOutput());
        }

        return $process->getOutput();
    }

    /**
     * 检查 PHP 版本是否满足要求
     *
     * @throws PhpVersionNotSupportedException 如果 PHP 版本太低
     */
    protected function checkPHPVersion(): void
    {
        $composerJson = json_decode(File::get(base_path('composer.json')), true);
        $phpVersion = PHP_VERSION;
        $requiredPhpVersion = Str::of($composerJson['require']['php'])->remove('^')->__toString();

        if (version_compare($phpVersion, $requiredPhpVersion, '<') && ! $this->ignorePlatformReqs) {
            throw new PhpVersionNotSupportedException("PHP $phpVersion 版本太低, 需要 PHP {$requiredPhpVersion}！如果想忽略版本要求，请先调用 ignorePlatFormReqs 方法。");
        }
    }

    /**
     * 设置忽略平台需求
     *
     * @return $this
     */
    public function ignorePlatFormReqs(): static
    {
        $this->ignorePlatformReqs = true;

        return $this;
    }
}
