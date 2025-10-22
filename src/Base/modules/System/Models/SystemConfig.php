<?php

declare(strict_types=1);

namespace Xditn\Base\modules\System\Models;

use Illuminate\Support\Facades\Cache;
use Xditn\Base\modules\System\Support\Configure;
use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $key
 * @property $value
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class SystemConfig extends Model
{
    protected $table = 'system_config';

    protected $fillable = ['id', 'key', 'value', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * 保存配置
     *
     * @param array $data
     *
     * @return Model|null
     */
    public function storeBy(array $data): ?Model
    {
        foreach ($data as $k => $value) {
            $config = $this->where('key', $k)->first();
            if (! $config) {
                parent::createBy([
                    'key' => $k,
                    'value' => $value,
                ]);
            } else {
                if ($config->value != $value) {
                    $this->where('key', $k)->update([
                        'value' => $value,
                    ]);
                }
            }
        }

        (new Configure())->cache();

        return true;
    }

    /**
     * 获取配置
     *
     * @param  string  $prefix
     * @param  string  $driver
     * @return array
     */
    public static function getConfig(string $prefix, string $driver = ''): array
    {
        if ($driver) {
            $prefix = $prefix.'.'.$driver;
        }

        $config = [];
        SystemConfig::query()->whereLike('key', $prefix)->get(['key', 'value'])
            ->each(function ($item) use (&$config) {
                $keys = explode('.', $item->key);
                $config[array_pop($keys)] = $item->value;
            });

        return $config;
    }

    /**
     * bootstrap to load config
     *
     * @return mixed
     */
    public static function loaded(): mixed
    {
        if (! Cache::get('system_config')) {
            (new self())->cache();
        }

        return Cache::get('system_config');
    }
}
