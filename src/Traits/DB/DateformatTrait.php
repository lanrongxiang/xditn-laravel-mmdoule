<?php

namespace Xditn\Traits\DB;

use Carbon\Carbon;
use DateTimeInterface;

trait DateformatTrait
{
    /**
     * @var string
     */
    protected string $timeFormat = 'Y-m-d H:i:s';

    /**
     * 设置时间格式
     *
     * @param  string  $timeFormat
     * @return $this
     */
    public function setTimeFormat(string $timeFormat): static
    {
        $this->timeFormat = $timeFormat;

        return $this;
    }

    /**
     * 重写 serializeDate
     */
    protected function serializeDate(DateTimeInterface|string $date): ?string
    {
        if (is_string($date)) {
            return $date;
        }

        // 获取时间戳值
        $timestamp = $date->getTimestamp();

        // 从时间戳创建 Carbon 实例并设置时区
        return Carbon::createFromTimestamp($timestamp, config('app.timezone'))
                     ->format($this->timeFormat);
    }
}
