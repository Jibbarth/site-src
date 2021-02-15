<?php

declare(strict_types=1);

namespace App\Collection;

use App\Model\Badge;
use Ramsey\Collection\AbstractCollection;

final class BadgeCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Badge::class;
    }

    public function shuffle(): self
    {
        $collection = clone $this;

        \shuffle($collection->data);

        return $collection;
    }
}
