<?php

namespace Xditn\Traits\DB;

/**
 * base operate
 */
trait WithSearch
{
    /**
     * @var array
     */
    public array $searchable = [];

    public ?\Closure $quickSearchCallback = null;

    /**
     * @param  array  $searchable
     * @return $this
     */
    public function setSearchable(array $searchable): static
    {
        $this->searchable = array_merge($this->searchable, $searchable);

        return $this;
    }

    /**
     * 设置快速搜索回调，用于转换数据
     *
     * @return $this
     */
    public function setQuickSearchCallback(\Closure $callback): static
    {
        $this->quickSearchCallback = $callback;

        return $this;
    }
}
