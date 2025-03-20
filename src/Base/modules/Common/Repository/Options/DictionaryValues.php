<?php

namespace Xditn\Base\modules\Common\Repository\Options;

use Illuminate\Support\Collection;
use Xditn\Base\modules\System\Models\DictionaryValues as DictionaryValuesModel;
use Xditn\Enums\Status;

class DictionaryValues implements OptionInterface
{
    public function get(): array|Collection
    {
        $dictionary = [];
        // TODO: Implement get() method.
        DictionaryValuesModel::where('status', Status::Enable->value())
            ->where('dic_id', request()->get('dic_id'))
            ->get()
            ->each(function ($item) use (&$dictionary) {
                $dictionary[] = [
                    'label' => $item->label,
                    'value' => $item->value,
                ];
            });

        return $dictionary;
    }
}
