<?php

declare(strict_types=1);

namespace App\Repository;

use App\Collection\BadgeCollection;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

#[AsAlias(BadgeRepositoryInterface::class)]
final class ChainBadgeRepository implements BadgeRepositoryInterface
{
    /**
     * @param iterable<\App\Repository\BadgeRepositoryInterface> $repositories
     */
    public function __construct(
        #[TaggedIterator('app.badge_repository')]
        private iterable $repositories
    ) {
    }

    public function getBadges(): BadgeCollection
    {
        $badges = new BadgeCollection();
        $allBadges = [];

        foreach ($this->repositories as $repository) {
            $allBadges[] = $repository->getBadges();
        }

        $badges = $badges->merge(...$allBadges);
        \assert($badges instanceof BadgeCollection);

        return $badges;
    }

    public function getCategory(): string
    {
        throw new \LogicException('ChainBadgeRepository not implement get category');
    }
}
