<?php

namespace Xditn\Base\modules\Common\Repository\Options;

use Illuminate\Support\Collection;
use Xditn\Base\modules\System\Models\Dictionary;
use Xditn\Enums\Status;

class Dictionaries implements OptionInterface
{
    public function get(): array|Collection
    {
        $dictionary = [];
        // TODO: Implement get() method.
        Dictionary::where('status', Status::Enable->value())
            ->get()
            ->each(function ($item) use (&$dictionary) {
               $dictionary[] = [
                   'label' => $item->name,
                   'value' => $item->id,
               ];
            });

        return $dictionary;
    }
}
