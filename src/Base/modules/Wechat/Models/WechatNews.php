<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Wechat\Models;

use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $title
 * @property $content
 * @property $thumb_media_id
 * @property $content_source_url
 * @property $author
 * @property $digest
 * @property $show_cover_pic
 * @property $need_open_comment
 * @property $only_fans_can_comment
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class WechatNews extends Model
{
    protected $table = 'wechat_news';

    protected $fillable = ['id', 'title', 'content', 'thumb_media_id', 'content_source_url', 'author', 'digest', 'show_cover_pic', 'need_open_comment', 'only_fans_can_comment', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];

    protected array $fields = ['id', 'title', 'content', 'thumb_media_id', 'content_source_url', 'author', 'digest', 'show_cover_pic', 'need_open_comment', 'only_fans_can_comment', 'created_at'];

    protected array $form = ['title', 'content', 'thumb_media_id', 'content_source_url', 'author', 'digest', 'show_cover_pic', 'need_open_comment', 'only_fans_can_comment'];

    public array $searchable = [
        'title' => 'like',

    ];
}
