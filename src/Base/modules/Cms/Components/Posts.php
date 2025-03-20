<?php

namespace Xditn\Base\modules\Cms\Components;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Xditn\Base\Modules\Cms\Models\Post;

/**
 * 文章组件
 */
class Posts extends Component
{
    public mixed $posts;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public ?int $categoryId = null,
        public int $limit = 10,
        public ?string $order = 'id:desc',
        public ?string $with = null
    ) {
        $this->posts = $this->getPosts();
    }

    /**
     * @return LengthAwarePaginator
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getPosts(): LengthAwarePaginator
    {
        return Post::query()

                    ->when($this->categoryId, fn ($query) => $query->where('category_id', $this->categoryId))

                    ->when($this->order, function ($query) {
                        [$field, $order] = explode(':', $this->order);
                        $query->orderBy($field, $order);
                    })

                    ->with($this->with ? explode(',', $this->with) : [])

                    ->paginate(request()->get('limit', $this->limit));
    }
}
