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

    /**
     * @param  array  $searchable
     * @return $this
     */
    public function setSearchable(array $searchable): static
    {
        $this->searchable = array_merge($this->searchable, $searchable);

        return $this;
    }
}
