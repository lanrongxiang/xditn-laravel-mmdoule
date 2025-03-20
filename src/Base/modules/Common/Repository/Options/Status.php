<?php

namespace Xditn\Base\modules\Common\Repository\Options;

use Xditn\Enums\Status as StatusEnum;

class Status implements OptionInterface
{
    public function get(): array
    {
        return [
            [
                'label' => StatusEnum::Enable->name(),
                'value' => StatusEnum::Enable->value(),
            ],

            [
                'label' => StatusEnum::Disable->name(),
                'value' => StatusEnum::Disable->value(),
            ],
        ];
    }
}
