<?php

declare(strict_types=1);

namespace App\Repository;

use App\Collection\BadgeCollection;
use App\Storage\LocalImageStore;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Symfony\Component\String\u;

#[AsAlias(BadgeRepositoryInterface::class)]
final class ChainBadgeRepository implements BadgeRepositoryInterface
{
    /**
     * @param iterable<BadgeRepositoryInterface> $repositories
     */
    public function __construct(
        #[TaggedIterator('app.badge_repository')]
        private iterable $repositories,
        private LocalImageStore $imageStore,
        private HttpClientInterface $client,
    ) {}

    public function getBadges(): BadgeCollection
    {
        $badges = new BadgeCollection();
        $allBadges = [];

        foreach ($this->repositories as $repository) {
            $allBadges[] = $repository->getBadges();
        }

        $badges = $badges->merge(...$allBadges);
        \assert($badges instanceof BadgeCollection);

        $this->localImages($badges);

        return $badges;
    }

    public function getCategory(): string
    {
        throw new \LogicException('ChainBadgeRepository not implement get category');
    }

    private function localImages(BadgeCollection $badgeCollection): void
    {
        foreach ($badgeCollection as $badge) {
            if (!str_starts_with($badge->image, 'http')) {
                continue;
            }
            $img = $this->client->request('GET', $badge->image)->getContent(false);
            $filename = u($badge->name)
                ->prepend($badge->category)
                ->snake()
                ->ensureEnd('.png')
                ->toString();

            $badge->image = $this->imageStore->store($img, $filename, 'badges/' . $badge->category);
        }
    }
}
