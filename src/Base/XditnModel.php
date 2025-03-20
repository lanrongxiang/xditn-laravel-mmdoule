<?php

namespace Xditn\Base;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Xditn\Support\DB\SoftDelete;
use Xditn\Traits\DB\BaseOperate;
use Xditn\Traits\DB\DateformatTrait;
use Xditn\Traits\DB\ScopeTrait;
use Xditn\Traits\DB\TransTraits;
use Xditn\Traits\DB\WithAttributes;

/**
 * @mixin Builder
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
abstract class XditnModel extends Model
{
    use BaseOperate;
    use DateformatTrait;
    use ScopeTrait;
    use SoftDeletes;
    use TransTraits;
    use WithAttributes;

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
     * 关闭自动时间戳
     *
     * @var bool
     */
    public $timestamps = false;

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
     * @param  array  $attributes
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

        $this->exists = true;

        $result = $this->save();

        $this->fireModelEvent('restored', false);

        return $result;
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
     * 重写日期序列化方法，将日期格式化为 ISO8601 字符串
     *
     * @param  DateTimeInterface  $date
     * @return string|null
     */
    protected function serializeDate(DateTimeInterface $date): ?string
    {
        return Carbon::instance($date)->toISOString(true);
    }
}
