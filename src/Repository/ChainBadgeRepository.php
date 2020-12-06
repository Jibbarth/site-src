<?php

declare(strict_types=1);

namespace App\Repository;

use App\Collection\BadgeCollection;

final class ChainBadgeRepository implements BadgeRepositoryInterface
{
    /**
     * @var array<\App\Repository\BadgeRepositoryInterface>
     */
    private array $repositories;

    /**
     * @param iterable<\App\Repository\BadgeRepositoryInterface> $badgeRepositories
     */
    public function __construct(iterable $badgeRepositories)
    {
        \assert($badgeRepositories instanceof \Traversable);
        $this->repositories = \array_filter(
            \iterator_to_array($badgeRepositories),
            static fn ($repository) => !$repository instanceof self
        );
    }

    public function getBadges(): BadgeCollection
    {
        $badges = new BadgeCollection();
        $allBadges = [];
        /** @var BadgeRepositoryInterface $repository */
        foreach ($this->repositories as $repository) {
            $allBadges[] = $repository->getBadges();
        }

        $badges = $badges->merge(...$allBadges);
        \assert($badges instanceof BadgeCollection);

        return $badges;
    }
}
