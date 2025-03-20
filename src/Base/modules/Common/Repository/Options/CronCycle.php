<?php

namespace Xditn\Base\modules\Common\Repository\Options;

class CronCycle implements OptionInterface
{
    public function get(): array
    {
        return [
            [
                'label' => '每分钟',
                'value' => 'everyMinute',
            ],
            [
                'label' => '每两分钟',
                'value' => 'everyTwoMinutes',
            ],
            [
                'label' => '每三分钟',
                'value' => 'everyThreeMinutes',
            ],
            [
                'label' => '每四分钟',
                'value' => 'everyFourMinutes',
            ],
            [
                'label' => '每五分钟',
                'value' => 'everyFiveMinutes',
            ],
            [
                'label' => '每十分钟',
                'value' => 'everyTenMinutes',
            ],
            [
                'label' => '每十五分钟',
                'value' => 'everyFifteenMinutes',
            ],
            [
                'label' => '每三十分钟',
                'value' => 'everyThirtyMinutes',
            ],
            [
                'label' => '每小时',
                'value' => 'hourly',
            ],
            [
                'label' => '每两小时',
                'value' => 'everyTwoHours',
            ],
            [
                'label' => '每三小时',
                'value' => 'everyThreeHours',
            ],
            [
                'label' => '每四小时',
                'value' => 'everyFourHours',
            ],
            [
                'label' => '每六小时',
                'value' => 'everySixHours',
            ],
            [
                'label' => '每天',
                'value' => 'daily',
            ],
            [
                'label' => '每天某时刻',
                'value' => 'dailyAt',
            ],
            [
                'label' => '每天两次任务',
                'value' => 'twiceDaily',
            ],
            [
                'label' => '每两分钟',
                'value' => 'everyTwoMinutes',
            ],
            [
                'label' => '每周',
                'value' => 'weekly',
            ],
            [
                'label' => '每周某时刻',
                'value' => 'weeklyOn',
            ],
            [
                'label' => '每月',
                'value' => 'monthly',
            ],
            [
                'label' => '每月某时刻',
                'value' => 'monthlyOn',
            ],
            [
                'label' => '每月某两天',
                'value' => 'twiceMonthly',
            ],
            [
                'label' => '每月最后一天',
                'value' => 'lastDayOfMonth',
            ],
            [
                'label' => '每季度',
                'value' => 'quarterly',
            ],
            [
                'label' => '每季度某时刻',
                'value' => 'quarterlyOn',
            ],
            [
                'label' => '每年',
                'value' => 'yearly',
            ],
        ];
    }
}
