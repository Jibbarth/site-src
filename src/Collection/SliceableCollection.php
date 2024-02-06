<?php

declare(strict_types=1);

namespace App\Collection;

use Ramsey\Collection\CollectionInterface;

interface SliceableCollection
{
    /** @phpstan-ignore-next-line */
    public function slice(int $offset, ?int $length = null): CollectionInterface;
}
