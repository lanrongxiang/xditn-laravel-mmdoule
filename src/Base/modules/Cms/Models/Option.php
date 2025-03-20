<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Cms\Models;

use Illuminate\Support\Str;
use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $key
 * @property $value
 * @property $autoload
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class Option extends Model
{
    //
    protected $table = 'cms_options';

    protected $fillable = [
        'id', 'key', 'value', 'autoload', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected array $keysOfBoolValue = [
        'site_comment_limit',
        'site_comment_need_email',
        'site_comment_need_login',
        'site_comment_nested',
        'site_comment_order_desc',
        'site_comment_check',
    ];

    /**
     * @return int
     */
    public function getValueAttribute(mixed $value): mixed
    {
        if (in_array($this->key, $this->keysOfBoolValue)) {
            return (bool) $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (Str::of($value)->isJson()) {
            return json_decode($value, true);
        }

        return $value;
    }

    /**
     * get values
     *
     * @return array|string|int
     */
    public static function getValues(string|array $keys): mixed
    {
        $values = [];

        // 批量查询
        if (is_string($keys) && $keys[mb_strlen($keys) - 1] == '*') {
            self::query()->where('key', 'like', str_replace('*', '%', $keys))->get()
                ->each(function ($item) use (&$values) {
                    $values[$item->key] = $item->value;
                });
        } else {
            self::when($keys != '*', function ($query) use ($keys) {
                if (is_string($keys)) {
                    if (Str::of($keys)->contains(',')) {
                        $keys = explode(',', $keys);
                    } else {
                        $keys = [$keys];
                    }
                }
                $query->whereIn('key', $keys);
            })
                ->get()
                ->each(function ($item) use (&$values) {
                    $values[$item->key] = $item->value;
                });

            if (count($values) === 1) {
                return $values[$keys];
            }
        }

        return $values;
    }
}
