<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Domain\Models;

use Xditn\Base\modules\Domain\Support\Request\AliyunRequest;
use Xditn\Base\modules\Domain\Support\Request\QCloudRequest;
use Xditn\Base\modules\Domain\Support\Request\Request;
use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $name
 * @property $name_servers
 * @property $expired_at
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class Domains extends Model
{
    protected $table = 'domain_domains';

    protected $fillable = ['id', 'name', 'name_servers', 'type', 'remark', 'expired_at', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * @var array
     */
    protected array $fields = ['id', 'name', 'type', 'name_servers', 'expired_at', 'remark', 'created_at', 'updated_at'];

    /**
     * @var array
     */
    protected array $form = ['name', 'type', 'remark'];

    /**
     * @var array
     */
    public array $searchable = [
        'name' => 'like',
    ];

    protected $casts = [
        'expired_at' => 'date:Y-m-d H:i:s',
    ];

    public const ALIYUN = 'aliyun';

    public const QCLOUD = 'qcloud';

    protected static function booted(): void
    {
        static::saved(function (Domains $domain) {
            $whois = $domain->api()->whois($domain->name);

            $domain->name_servers = $whois['name_servers'];
            $domain->expired_at = $whois['expired_at'];
            $domain->created_at = $whois['created_at'];
            // 这里需要静默处理，不然会递归
            $domain->saveQuietly();
        });
    }

    /**
     * @param  array  $data
     * @param  bool  $isUpdate
     * @return array
     */
    protected function filterData(array $data, $isUpdate = false): array
    {
        $_data = [];

        foreach ($data as $k => $val) {
            if (in_array($k, $this->form)) {
                $_data[$k] = $val;
            }
        }

        return parent::filterData($_data, $isUpdate);
    }

    /**
     * 是否是阿里云
     *
     * @return bool
     */
    public function isAliYun(): bool
    {
        return $this->type == self::ALIYUN;
    }

    /**
     * 是否是腾讯云
     *
     * @return bool
     */
    public function isQCloud(): bool
    {
        return $this->type == self::QCLOUD;
    }

    /**
     * 域名请求
     *
     * @return Request
     */
    public function api(): Request
    {
        return $this->isAliYun() ? new AliyunRequest() : new QCloudRequest();
    }
}
