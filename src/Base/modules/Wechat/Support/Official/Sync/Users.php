<?php

namespace Xditn\Base\modules\Wechat\Support\Official\Sync;

use Illuminate\Support\Collection;
use Laravel\Octane\Exceptions\DdException;
use Xditn\Base\modules\Wechat\Models\WechatUsers;
use Xditn\Base\modules\Wechat\Support\Official\Official;

class Users extends Official
{
    protected array $openIds = [];

    protected int $total = 0;

    public function start()
    {
        $latest = $this->getLastUser();

        $this->syncOpenIds($latest?->openid);

        $this->syncUsers();
    }

    protected function getLastUser(): ?WechatUsers
    {
        return WechatUsers::orderByDesc('id')->first();
    }

    /**
     * @throws DdException
     */
    protected function syncOpenIds(?string $nextOpenid): void
    {
        // 这里的通过 openid 集合和微信返回的 total 进行判断
        if ($this->total) {
            if (count($this->openIds) >= $this->total) {
                return;
            }
        }

        $userOpenIds = $this->getWechatUserOpenids($nextOpenid);
        if (! $this->total) {
            $this->total = $userOpenIds['total'];
        }

        $this->openIds = array_merge($this->openIds, $userOpenIds['data']['openid']);

        if ($userOpenIds['next_openid']) {
            $this->syncOpenIds($userOpenIds['next_openid']);
        } else {
            $this->openIds = array_merge($this->openIds, $userOpenIds['data']['openid']);
        }
    }

    protected function syncUsers(): void
    {
        Collection::make($this->openIds)
            ->chunk(100)
            ->each(function ($items) {
                $openIds = [];

                $items->each(function ($item) use (&$openIds) {
                    $openIds[] = ['openid' => $item];
                });

                $users = $this->postJson('user/info/batchget', [
                    'user_list' => $openIds,
                ]);

                if (! empty($users['user_info_list'])) {
                    $this->syncToDatabase($users['user_info_list']);
                }
            });
    }

    protected function syncToDatabase($users): void
    {
        foreach ($users as &$user) {
            $user['avatar'] = $user['headimgurl'];
            $user['unionid'] = $user['unionid'] ?? '';
            $user['created_at'] = time();
            $user['updated_at'] = time();
            unset($user['headimgurl'], $user['tagid_list']);
            unset($user['qr_scene'], $user['qr_scene_str']);
        }
        WechatUsers::insert($users);
    }

    public function getWechatUserOpenIds(?string $nextOpenId): mixed
    {
        return $this->get('user/get', [
            'next_openid' => $nextOpenId,
        ]);
    }
}
