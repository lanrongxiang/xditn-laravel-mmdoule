<?php

declare(strict_types=1);

namespace Xditn\Contracts;

interface RelationAttribute
{
    public function relationName(): string;

    /**
     * @return array
     */
    public function relationArguments(): array;
}
