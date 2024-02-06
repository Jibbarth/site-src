<?php

declare(strict_types=1);

namespace App\Collection;

use Ramsey\Collection\CollectionInterface;

trait SliceTrait
{
    /** @phpstan-ignore-next-line */
    public function slice(int $offset, ?int $length = null): CollectionInterface
    {
        $collection = clone $this;
        $collection->data = array_merge([], \array_slice($this->data, $offset, $length, true));

        return $collection;
    }
}
