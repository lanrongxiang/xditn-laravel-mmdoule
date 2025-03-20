<?php

declare(strict_types=1);

namespace Xditn\Base\modules\System\Models;

use Illuminate\Database\Eloquent\Collection;
use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $platform
 * @property $webhook
 * @property $status
 * @property $secret
 * @property $creator_id
 * @property $msg_type
 * @property $content
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class Webhooks extends Model
{
    protected $table = 'system_webhooks';

    protected $fillable = ['id', 'platform', 'webhook', 'event', 'status', 'secret', 'msg_type', 'content', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    protected array $fields = ['id', 'platform', 'webhook', 'event', 'status', 'secret', 'msg_type', 'created_at', 'updated_at'];

    protected array $form = ['platform', 'webhook', 'status', 'event', 'msg_type', 'content', 'created_at'];

    public array $searchable = [
        'platform' => '=',
        'status' => '=',
    ];

    // 平台
    public const DINGTALK = 1;

    public const FEISHU = 2;

    public const WECHAT = 3;

    // 消息类型
    public const TEXT_MSG = 'text';

    public const MARKDOWN_MSG = 'markdown';

    // 推送事件
    public const EXCEPTION_EVENT = 'exception';

    // 接口告警通知
    public const CONNECTOR_REQUEST_TIMEOUT = 'connector_request_timeout'; // 超时

    public const CONNECTOR_REQUEST_ERROR = 'connector_request_error'; // 请求错误

    public const CONNECTOR_IP_EXCEPTION = 'connector_ip_exception'; // 请求 IP 异常

    public const CONNECTOR_REQUEST_MAX = 'connector_request_max'; // 请求超量

    /**
     * 异常事件
     */
    public static function exceptions(): Collection
    {
        return self::where('event', self::EXCEPTION_EVENT)->get();
    }

    /**
     * @return Collection|\Illuminate\Support\Collection|Webhooks[]
     */
    public static function connectorRequestTimeout()
    {
        return self::where('event', self::CONNECTOR_REQUEST_TIMEOUT)->get();
    }

    /**
     * @return Collection|\Illuminate\Support\Collection|Webhooks[]
     */
    public static function connectorRequestError()
    {
        return self::where('event', self::CONNECTOR_REQUEST_ERROR)->get();
    }

    /**
     * @return Collection|\Illuminate\Support\Collection|Webhooks[]
     */
    public static function connectorIpException()
    {
        return self::where('event', self::CONNECTOR_IP_EXCEPTION)->get();
    }

    /**
     * @return Collection|\Illuminate\Support\Collection|Webhooks[]
     */
    public static function connectorRequestMax()
    {
        return self::where('event', self::CONNECTOR_REQUEST_MAX)->get();
    }
}
