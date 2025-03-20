<?php

declare(strict_types=1);

namespace Xditn\Base\modules\Cms\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Request;
use Xditn\Base\modules\Cms\Enums\Visible;
use Xditn\Base\XditnModel as Model;

/**
 * @property $id
 * @property $category_id
 * @property $author
 * @property $title
 * @property $content
 * @property $excerpt
 * @property $status
 * @property $is_can_comment
 * @property $password
 * @property $order
 * @property $user_id
 * @property $type
 * @property $comment_count
 * @property $seo_title
 * @property $seo_keywords
 * @property $seo_description
 * @property $creator_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 */
class Post extends Model
{
    protected $table = 'cms_posts';

    protected $fillable = ['id', 'category_id', 'author', 'title', 'cover', 'content', 'excerpt', 'status', 'is_can_comment', 'visible', 'top', 'password', 'order', 'user_id', 'type', 'comment_count', 'creator_id', 'created_at', 'updated_at', 'deleted_at', 'seo_title', 'seo_keywords', 'seo_description'];

    protected array $fields = ['id', 'author', 'title', 'status', 'is_can_comment', 'password', 'order', 'created_at', 'top', 'seo_title', 'seo_keywords', 'seo_description'];

    protected array $form = ['category_id', 'author', 'title', 'cover', 'content', 'excerpt', 'status', 'visible', 'top', 'is_can_comment', 'password', 'order', 'seo_title', 'seo_keywords', 'seo_description'];

    public array $searchable = [
        'title' => 'like',
        'category_id' => '=',
        'type' => '=',
    ];

    /**
     * booted
     */
    public static function booted()
    {
        // 如果不是密码查看，密码置空
        static::saving(function (Post $post) {
            if (! Visible::PASSWORD->assert((int) $post->visible)) {
                $post->password = '';
            }
        });

        // 保存后
        static::saved(function (Post $post) {
            (new static())->savePostTags($post);
        });
    }

    /**
     * tags
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tags::class, PostHasTags::class, 'post_id', 'tag_id');
    }

    /**
     * save tags
     */
    public function savePostTags(Post $post): void
    {
        $tagNames = Request::get('tags');

        if (! $tagNames) {
            return;
        }

        $tags = Tags::getTagsByNames($tagNames);
        $existTagNames = $tags->pluck('name');

        $tagIds = $tags->pluck('id')->toArray();
        foreach ($tagNames as $tagName) {
            if (! $existTagNames->contains($tagName)) {
                $tagIds[] = app(Tags::class)->storeBy(['name' => $tagName]);
            }
        }

        $post->tags()->sync($tagIds);
    }

    /**
     * parse int author
     */
    public function author(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => intval($value),
            set: fn ($value) => intval($value)
        );
    }

    /**
     * 封面
     */
    protected function cover(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value)
        );
    }
}
