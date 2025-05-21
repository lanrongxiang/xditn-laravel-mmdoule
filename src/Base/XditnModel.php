<?php

namespace Xditn\Base;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Xditn\Support\DB\SoftDelete;
use Xditn\Traits\DB\BaseOperate;
use Xditn\Traits\DB\DateformatTrait;
use Xditn\Traits\DB\ScopeTrait;
use Xditn\Traits\DB\TransTraits;
use Xditn\Traits\DB\WithAttributes;

/**
 * @method static EloquentBuilder |static today(string $column = 'created_at', ?string $timezone = null)
 * @method static EloquentBuilder |static byDate(string $column, $date, ?string $timezone = null)
 * @method static EloquentBuilder |static dateRange(array $columns = ['created_at'], array $options = [])
 * @mixin EloquentBuilder
 * @mixin QueryBuilder
 */
abstract class XditnModel extends Model
{
    use BaseOperate;
    use DateformatTrait;
    use ScopeTrait;
    use TransTraits;
    use WithAttributes;

    /**
     * 关闭自动时间戳
     *
     * @var bool
     */
    public $timestamps = false;
    /**
     * 日期格式为 Unix 时间戳
     *
     * @var string
     */
    protected $dateFormat = 'U';
    /**
     * 分页默认每页显示的记录数
     *
     * @var int
     */
    protected $perPage = 10;
    /**
     * 默认字段类型转换
     *
     * @var array
     */
    protected array $defaultCasts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
    ];

    /**
     * 默认隐藏的字段
     *
     * @var array
     */
    protected array $defaultHidden = ['deleted_at'];

    /**
     * 构造函数
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->init();
    }

    /**
     * 初始化设置
     */
    protected function init(): void
    {
        // 隐藏指定字段
        $this->makeHidden($this->defaultHidden);
        // 合并默认字段类型转换
        $this->mergeCasts($this->defaultCasts);
        // 自动设置数据范围，如果使用了 DataRange trait
        foreach (class_uses_recursive(static::class) as $trait) {
            if (str_contains($trait, 'DataRange')) {
                $this->setDataRange();
            }
            if (str_contains($trait, 'ColumnAccess')) {
                $this->setColumnAccess();
            }
        }
    }

    /**
     * 启用软删除
     *
     * @return void
     */
    public static function bootSoftDeletes(): void
    {
        static::addGlobalScope(new SoftDelete());
    }

    /**
     * 覆盖 restore 方法
     *
     * 修改 deleted_at 默认值
     */
    public function restore(): bool
    {
        if ($this->fireModelEvent('restoring') === false) {
            return false;
        }
        $this->{$this->getDeletedAtColumn()} = 0;
        $this->exists                        = true;
        $result                              = $this->save();
        $this->fireModelEvent('restored', false);
        return $result;
    }

    public function scopeDateRange($query, array $columns = ['created_at'], array $options = []): mixed
    {
        return $query->when(!empty($columns), function ($q) use ($columns, $options)
        {
            foreach ($columns as $column) {
                // 从请求中获取 start/end 时间（支持自定义后缀）
                $startSuffix = $options['start_suffix'] ?? '_start_at';
                $endSuffix   = $options['end_suffix'] ?? '_end_at';
                $start       = request()->input("{$column}{$startSuffix}");
                $end         = request()->input("{$column}{$endSuffix}");
                // 如果时间范围无效则跳过
                if (empty($start) && empty($end)) {
                    continue;
                }
                // 构建时间范围查询
                $q->where(function ($subQuery) use ($column, $start, $end)
                {
                    // 处理开始时间
                    if (!empty($start)) {
                        $subQuery->where(
                            $column,
                            '>=',
                            $this->parseDateTime($column, $start, true)
                        );
                    }
                    // 处理结束时间
                    if (!empty($end)) {
                        $subQuery->where(
                            $column,
                            '<=',
                            $this->parseDateTime($column, $end, false)
                        );
                    }
                });
            }
            return $q;
        });
    }

    /**
     * 统一时间格式转换（根据字段类型自动处理）
     *
     * @param string $column      字段名
     * @param string $value       时间字符串
     * @param bool   $isStartTime 是否为起始时间
     *
     * @return Carbon|int
     */
    protected function parseDateTime(string $column, string $value, bool $isStartTime)
    {
        // 特殊处理 created_at 时间戳字段
        if ($column === 'created_at') {
            $timestamp = strtotime($value);
            return $isStartTime ? $timestamp : strtotime('+0 day', $timestamp) - 1;
        }
        // 其他字段按日期处理
        $carbon = Carbon::parse($value);
        return $isStartTime ? $carbon->startOfDay() : $carbon->endOfDay();
    }

    /**
     * 重写日期序列化方法，将日期格式化为 ISO8601 字符串
     *
     * @param DateTimeInterface $date
     *
     * @return string|null
     */
    protected function serializeDate(DateTimeInterface $date): ?string
    {
        return Carbon::instance($date)->toISOString(true);
    }


    /**
     * 时间戳日期范围查询（增强类型提示）
     *
     * @param EloquentBuilder $query
     * @param string          $column
     * @param mixed           $date
     * @param string|null     $timezone
     *
     * @return EloquentBuilder
     */
    public function scopeByDate(
        EloquentBuilder $query,
        string $column,
        $date,
        ?string $timezone = null
    ): EloquentBuilder{
        $date  = Carbon::parse($date, $timezone);
        $start = $date->copy()->startOfDay()->timestamp;
        $end   = $date->copy()->endOfDay()->timestamp;
        return $query->whereBetween($column, [$start, $end]);
    }

    /**
     * 当天数据查询
     */
    public function scopeToday(
        EloquentBuilder $query,
        string $column = 'created_at',
        ?string $timezone = null
    ): EloquentBuilder{
        return $this->scopeByDate($query, $column, now($timezone), $timezone);
    }

}
