<?php

namespace Xditn\Base\modules\Common\Repository\Options;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Schemas implements OptionInterface
{
    public function get(): array
    {
        $options = [];

        $tablePrefix = DB::connection()->getTablePrefix();

        foreach (Schema::getTables() as $table) {
            $tableName = Str::of($table['name'])->remove($tablePrefix);

            $options[] = [
                'label' => $tableName."\t\t\t\t".$table['comment'],
                'value' => $tableName,
            ];
        }

        return $options;
    }
}
