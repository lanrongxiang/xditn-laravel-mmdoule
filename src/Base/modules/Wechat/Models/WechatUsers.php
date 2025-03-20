<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Wechat\Models;

use Xditn\Base\XditnModel as Model;

/**
 * @property $nickname
 * @property $avatar
 * @property $openid
 * @property $language
 * @property $country
 * @property $province
 * @property $city
 * @property $subscribe
 * @property $subscribe_time
 * @property $subscribe_scene
 * @property $unionid
 * @property $sex
 * @property $remark
 * @property $groupid
 * @property $tagid_list
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class WechatUsers extends Model
{
    protected $table = 'wechat_users';

    protected $fillable = ['nickname', 'avatar', 'openid', 'language', 'country', 'province', 'city', 'subscribe', 'subscribe_time', 'subscribe_scene', 'unionid', 'sex', 'remark', 'groupid', 'tagid_list', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    protected array $fields = ['nickname', 'avatar', 'openid', 'language', 'country', 'province', 'city', 'subscribe', 'subscribe_time', 'sex'];
}
