<?php

namespace Xditn\Base\modules\User\Listeners;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Xditn\Base\modules\User\Events\Login as Event;
use Xditn\Base\modules\User\Models\LogLogin;
use Xditn\Enums\Status;
use Xditn\Support\IP;

class Login
{
    /**
     * Handle the event.
     */
    public function handle(Event $event): void
    {
        $request = $event->request;

        $this->log($request, (bool) $event->user, $event->token);

        if ($event->user) {
            $event->user->login_ip = $request->ip();
            $event->user->login_at = time();
            $event->user->remember_token = null;
            $event->user->save();
        }
    }

    /**
     * login log
     */
    protected function log(Request $request, int $isSuccess, ?string $token): void
    {
        $tokenId = 0;
        if ($token) {
            [$tokenId] = explode('|', $token);
        }

        LogLogin::insert([
            'account' => $this->getAccount($request),
            'login_ip' => $request->ip(),
            'token_id' => $tokenId,
            'location' => IP::getLocation($request->ip()),
            'browser' => $this->getBrowserFrom(Str::of($request->userAgent())),
            'platform' => $this->getPlatformFrom(Str::of($request->userAgent())),
            'login_at' => time(),
            'status' => $isSuccess ? Status::Enable : Status::Disable,
        ]);
    }

    /**
     * get platform
     */
    protected function getBrowserFrom(Stringable $userAgent): string
    {
        return match (true) {
            $userAgent->contains('MSIE', true) => 'IE',
            $userAgent->contains('Firefox', true) => 'Firefox',
            $userAgent->contains('Chrome', true) => 'Chrome',
            $userAgent->contains('Opera', true) => 'Opera',
            $userAgent->contains('Safari', true) => 'Safari',
            default => 'unknown'
        };
    }

    /**
     * get os name
     */
    protected function getPlatformFrom(Stringable $userAgent): string
    {
        return match (true) {
            $userAgent->contains('win', true) => 'Windows',
            $userAgent->contains('mac', true) => 'Mac OS',
            $userAgent->contains('linux', true) => 'Linux',
            $userAgent->contains('iphone', true) => 'iphone',
            $userAgent->contains('android', true) => 'Android',
            default => 'unknown'
        };
    }

    /**
     * @return mixed|string
     */
    protected function getAccount(Request $request)
    {
        if ($account = $request->get('account')) {
            return $account;
        }

        if ($mobile = $request->get('mobile')) {
            return $mobile;
        }

        return '未知';
    }
}
