<?php

namespace Xditn\Base\modules\Common\Repository\Options;

use Xditn\Base\modules\Permissions\Enums\DataRange as DataRangeEnum;

class DataRange implements OptionInterface
{
    public function get(): array
    {
        return [
            [
                'label' => DataRangeEnum::All_Data->name(),
                'value' => DataRangeEnum::All_Data->value(),
            ],

            [
                'label' => DataRangeEnum::Personal_Choose->name(),
                'value' => DataRangeEnum::Personal_Choose->value(),
            ],

            [
                'label' => DataRangeEnum::Personal_Data->name(),
                'value' => DataRangeEnum::Personal_Data->value(),
            ],

            [
                'label' => DataRangeEnum::Department_Data->name(),
                'value' => DataRangeEnum::Department_Data->value(),
            ],

            [
                'label' => DataRangeEnum::Department_DOWN_Data->name(),
                'value' => DataRangeEnum::Department_DOWN_Data->value(),
            ],
        ];
    }
}
