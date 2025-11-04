<?php

namespace Xditn\Listeners;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Xditn\Enums\Code;
use Xditn\Support\ResponseBuilder;

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
            // 自定义响应内容
            if ($response instanceof ResponseBuilder) {
                $event->response = $response();
            } elseif ($response instanceof JsonResponse) {
                $exception = $response->exception;
                if ($response->getStatusCode() == SymfonyResponse::HTTP_OK && ! $exception) {
                    // 获取原始数据（true 参数确保返回数组而不是对象）
                    $rawData = $response->getData(true);
                    if (!$this->isAlreadyFormatted($rawData)) {
                        $response->setData($this->formatData($rawData));
                    }
                }
            }
        }
    }

    /**
     * 检查数据是否已经被格式化
     *
     * @param  mixed  $data
     * @return bool
     */
    protected function isAlreadyFormatted(mixed $data): bool
    {
        return is_array($data) && isset($data['code']) && isset($data['message']);
    }

    /**
     * 格式化响应数据
     *
     * @param  mixed  $data
     * @return array
     */
    protected function formatData(mixed $data): array
    {
        return array_merge(
            [
                'code' => Code::SUCCESS->value(),
                'message' => Code::SUCCESS->message(),
            ],
            format_response_data($data)
        );
    }
}
