<?php

use Illuminate\Database\Seeder;
use Xditn\Modules\User\Models\User;

return new class extends Seeder
{
    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(): void
    {
        $user = new User([
            'username' => 'xditnadmin',

            'email' => 'xditn@admin.com',

            'password' => 'xditnadmin',

            'creator_id' => 1,

            'department_id' => 0
        ]);

        $user->save();
    }
};
