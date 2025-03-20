<?php

use Illuminate\Database\Seeder;
use Xditn\Base\modules\Common\Support\ImportPermissions;

return new class extends Seeder
{
    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(): void
    {
        $menus = $this->menus();

        ImportPermissions::import($menus);
    }

    public function menus(): array
    {
        return [
        ];
    }
};
