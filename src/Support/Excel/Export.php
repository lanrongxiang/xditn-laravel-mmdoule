<?php

namespace Xditn\Support\Excel;

use Illuminate\Support\{Facades\Event, Facades\Storage, Str};
use Maatwebsite\Excel\{
    Concerns\FromArray,
    Concerns\ShouldAutoSize,
    Concerns\WithColumnWidths,
    Excel,
    Facades\Excel as ExcelFacade
};
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Xditn\Exceptions\FailedException;

/**
* 通用Excel导出抽象类
* 
 * @package Xditn\Support\Excel
* @abstract
 */
abstract class Export implements FromArray, ShouldAutoSize, WithColumnWidths
{
/** @var int 自动切换CSV格式的阈值（数据行数） */
    protected int $toCsvLimit = 20_000;

    /** @var array 导出的数据集合 */
    protected array $data = [];

    /** @var array 查询参数集合 */
    protected array $params = [];

    /** @var array 表格标题配置 */
    protected array $header = [];

    /** @var string|null 自定义文件名（不带扩展名） */
    protected ?string $filename = null;

    /** @var bool 是否解除内存限制 */
    protected bool $unlimitedMemory = false;

    /**
     * 执行导出操作并返回文件路径
     * 
     * @return string 导出的文件绝对路径
     * @throws FailedException 当导出过程发生错误时抛出
     */
    public function export(): string
    {
        try {
            $this->configureMemory();

            $writeType = $this->shouldUseCsv() ? 'csv' : 'xlsx';
            $filePath = $this->buildFilePath($writeType);

            ExcelFacade::store($this, $filePath, null, $writeType);

            Event::dispatch(\Xditn\Events\Excel\Export::class);

            return $filePath;
        } catch (\Throwable $e) {
            throw new FailedException("导出失败: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * 触发文件下载
     * 
     * @param string|null $filename 自定义下载文件名（包含扩展名）
     * @return BinaryFileResponse
     */
    public function download(?string $filename = null): BinaryFileResponse
    {
        $filename ??= $this->generateFilename();
        $writeType = $this->shouldUseCsv() ? 'csv' : 'xlsx';

        return ExcelFacade::download(
            $this,
            $filename,
            $writeType,
            $this->getDownloadHeaders($writeType)
        );
    }

    /**
     * 设置查询参数
     * 
     * @param array $params 查询条件参数
     * @return $this
     */
    public function setParams(array $params): static
    {
        $this->params = $params;
        return $this;
    }

    /**
     * 获取当前查询参数
     * 
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * 生成表格标题行
     * 
     * @return string[] 标题数组，例如 ['ID', '姓名', '创建时间']
     */
    public function headings(): array
    {
        return collect($this->header)
            ->filter(fn ($value, $key) => is_string($key) && is_numeric($value) || is_string($value))
            ->keys()
            ->all();
    }

    /**
     * 配置列宽
     * 
     * @return int[] 列宽配置数组，例如 ['A' => 20, 'B' => 30]
     */
    public function columnWidths(): array
    {
        return collect($this->header)
            ->filter(fn ($value, $key) => is_string($key) && is_numeric($value))
            ->mapWithKeys(fn ($width, $title, $index) => [
                chr(65 + $index) => (int)$width // 转换为ASCII字符(A,B,C...)
            ])
            ->all();
    }

    /**
     * 获取CSV格式配置
     * 
     * @return array 包含以下配置项：
     * - delimiter: 字段分隔符
     * - use_bom: 是否添加BOM头
     * - enclosure: 文本包围符
     */
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
            'use_bom' => false,
            'enclosure' => '"',
        ];
    }

    /**
     * 执行异步导出任务
     * 
     * @param array $params 查询参数
     * @return string 导出的文件路径
     */
    public function run(array $params): string
    {
        return $this->setParams($params)->export();
    }

    /**
     * 配置内存限制
     */
    protected function configureMemory(): void
    {
        if ($this->unlimitedMemory) {
            ini_set('memory_limit', '-1');
        }
    }

    /**
     * 判断是否使用CSV格式
     * 
     * @return bool 当数据量超过阈值时返回true
     */
    protected function shouldUseCsv(): bool
    {
        return count($this->data) >= $this->toCsvLimit;
    }

    /**
     * 构建文件存储路径
     * 
     * @param string $extension 文件扩展名（csv/xlsx）
     * @return string 文件绝对路径
     * @throws FailedException 当目录创建失败时抛出
     */
    protected function buildFilePath(string $extension): string
    {
        $filename = $this->generateFilename($extension);
        Storage::makeDirectory(dirname($filename));

        return Storage::path($filename);
    }

    /**
     * 生成标准文件名
     * 
     * @param string|null $extension 强制指定扩展名
     * @return string 格式：exports/YYYYMMDD/UUID.扩展名
     */
    protected function generateFilename(?string $extension = null): string
    {
        $extension ??= $this->shouldUseCsv() ? 'csv' : 'xlsx';

        return sprintf(
            'exports/%s/%s.%s',
            now()->format('Ymd'),
            $this->filename ?? Str::uuid(),
            $extension
        );
    }

    /**
     * 获取下载响应头
     * 
     * @param string $type 文件类型（csv/xlsx）
     * @return array HTTP头配置数组
     */
    protected function getDownloadHeaders(string $type): array
    {
        return match ($type) {
            'csv' => ['Content-Type' => 'text/csv'],
            default => ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        };
    }
}