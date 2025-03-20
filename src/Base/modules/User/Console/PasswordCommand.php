<?php

namespace Xditn\Base\modules\User\Console;

use Illuminate\Console\Command;
use Xditn\Base\modules\User\Models\User;

class PasswordCommand extends Command
{
    protected $signature = 'xditn:pwd';

    protected $description = '更新后台用户密码';

    public function handle(): void
    {
        if (config('app.debug')) {
            $email = $this->ask('👉 请输入修改用户的邮箱');

            $user = User::query()->where('email', $email)->first();

            if ($user) {
                $password = $this->ask('👉 请输入修改用户的密码');
                $user->password = $password;
                if ($user->save()) {
                    $this->info('修改密码成功');
                } else {
                    $this->info('修改密码失败');
                }
            } else {
                $this->error('未找到指定邮箱的用户');
            }
        }
    }
}
