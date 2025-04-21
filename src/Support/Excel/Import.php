<?php

namespace Xditn\Support\Excel;

use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\{Concerns\Importable, Concerns\ToCollection, Concerns\WithChunkReading, Concerns\WithEvents, Concerns\WithStartRow, Concerns\WithValidation, Events\BeforeImport, Validators\ValidationException};
use Xditn\Exceptions\FailedException;

/**
 * Excel 数据导入抽象类
 *
 * @package Xditn\Support\Excel
 * @abstract
 */
abstract class Import implements ToCollection, WithChunkReading, WithEvents, WithStartRow, WithValidation
{
    use Importable;

    /** @var int 总导入行数 */
    protected static int $total = 0;
    /** @var int 最大允许导入行数 */
    protected static int $importMaxNum = 5000;
    /** @var array 错误信息集合 */
    protected array $err = [];
    /** @var array 导入参数 */
    protected array $params = [];
    /** @var int 分块大小 */
    protected int $chunkSize = 200;
    /** @var int 起始行号 */
    protected int $start = 2;
    /** @var bool 是否异步导入 */
    protected bool $isAsync = false;

    /**
     * 导入前预处理
     *
     * @param BeforeImport $event 导入事件对象
     *
     * @throws FailedException 当超过最大导入行数时抛出
     */
    public static function beforeImport(BeforeImport $event): void
    {
        $total         = $event->getReader()->getTotalRows()['Worksheet'];
        static::$total = $total;
        if ($total > static::$importMaxNum) {
            throw new FailedException(
                sprintf('最大支持导入数量 %d 条', self::$importMaxNum)
            );
        }
    }

    /**
     * 获取当前参数
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * 设置导入参数
     *
     * @param array $params 参数数组
     *
     * @return $this
     */
    public function setParams(array $params): static
    {
        $this->params = $params;
        return $this;
    }

    /**
     * 获取分块大小
     *
     * @return int
     */
    public function chunkSize(): int
    {
        return $this->chunkSize;
    }

    /**
     * 获取起始行号
     *
     * @return int
     */
    public function startRow(): int
    {
        return $this->start;
    }

    /**
     * 定义验证规则
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * 启用异步模式
     *
     * @return $this
     */
    public function async(): static
    {
        $this->isAsync = true;
        return $this;
    }

    /**
     * 执行异步导入任务
     *
     * @param array $params 任务参数
     *
     * @return mixed 导入结果
     */
    public function run(array $params): mixed
    {
        return $this->setParams($params)->import($params['path']);
    }

    /**
     * 执行文件导入
     *
     * @param string|UploadedFile $filePath   文件路径或上传文件对象
     * @param string|null         $disk       存储磁盘名称
     * @param string|null         $readerType 读取器类型
     *
     * @return int|array|static
     * @throws FailedException
     *
     * @psalm-return (
     *     $this->isAsync is true
     *     ? static
     *     : array{error: list<string>, total: int, path: string}|int
     * )
     */
    public function import(
        string|UploadedFile $filePath,
        ?string $disk = null,
        ?string $readerType = null
    ): int|array|static{
        // 参数校验
        if (empty($filePath)) {
            throw new FailedException('未上传导入文件');
        }
        // 处理上传文件
        if ($filePath instanceof UploadedFile) {
            $filePath = $this->storeUploadedFile($filePath);
        }
        // 异步处理直接返回实例
        if ($this->isAsync) {
            $this->params['path'] = $filePath;
            return $this;
        }
        // 执行同步导入
        return $this->processSyncImport($filePath, $disk, $readerType);
    }

    /**
     * 存储上传文件
     *
     * @param UploadedFile $file 上传文件实例
     *
     * @return string 存储路径
     */
    protected function storeUploadedFile(UploadedFile $file): string
    {
        return $file->store(
            'excel/import/' . date('Ymd'),
            'local'
        );
    }

    /**
     * 处理同步导入逻辑
     *
     * @param string      $filePath   文件存储路径
     * @param string|null $disk       存储磁盘
     * @param string|null $readerType 读取器类型
     *
     * @return array|int
     */
    protected function processSyncImport(
        string $filePath,
        ?string $disk,
        ?string $readerType
    ): array|int{
        try {
            $this->getImporter()->import(
                $this,
                $filePath,
                $disk ?? $this->disk,
                $readerType ?? $this->readerType
            );
            return static::$total;
        } catch (ValidationException $e) {
            return $this->formatValidationErrors($e, $filePath);
        }
    }

    /**
     * 格式化验证错误信息
     *
     * @param ValidationException $e        验证异常对象
     * @param string              $filePath 文件路径
     *
     * @return array 格式化后的错误信息
     */
    protected function formatValidationErrors(
        ValidationException $e,
        string $filePath
    ): array{
        $this->err = [];
        foreach ($e->failures() as $failure) {
            $this->err[] = sprintf(
                '第%d行错误: %s',
                $failure->row(),
                implode(' | ', $failure->errors())
            );
        }
        return [
            'error' => $this->err,
            'total' => static::$total,
            'path'  => $filePath,
        ];
    }
}