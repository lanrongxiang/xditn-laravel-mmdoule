<?php

use Illuminate\Database\Seeder;
use Xditn\Base\modules\User\Models\User;

return new class() extends Seeder
{
    /**
     * Run the seeder.
     */
    public function run(): void
    {
        $user = new User([
            'username' => 'xditnadmin',
            'email' => 'xditn@admin.com',
            'password' => 'xditnadmin',
            'creator_id' => 1,
            'department_id' => 0,
        ]);

        $user->save();
    }
};
