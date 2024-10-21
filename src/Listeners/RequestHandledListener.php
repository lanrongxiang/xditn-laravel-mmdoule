<?php

namespace Xditn\Listeners;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Xditn\Enums\Code;

/**
 * 请求处理监听器
 *
 * 监听请求处理并格式化响应数据
 */
class RequestHandledListener
{
    /**
     * 处理事件
     *
     * @param  RequestHandled  $event
     * @return void
     */
    public function handle(RequestHandled $event): void
    {
        if (isRequestFromDashboard()) {
            $response = $event->response;

            if ($response instanceof JsonResponse) {
                $exception = $response->exception;

                if ($response->getStatusCode() == SymfonyResponse::HTTP_OK && ! $exception) {
                    $response->setData($this->formatData($response->getData()));
                }
            }
        }
    }

    /**
     * 格式化响应数据
     *
     * @param  mixed  $data
     * @return array
     */
    protected function formatData(mixed $data): array
    {
        return [
            'code' => Code::SUCCESS,
            'data' => $data,
        ];
    }
}
