<?php

declare(strict_types=1);

namespace App\Repository\Badge;

use App\Collection\BadgeCollection;
use App\Model\Badge;
use App\Repository\BadgeRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HolopinBadge implements BadgeRepositoryInterface
{
    private const HOLOPIN_URL = 'https://holopin.io/';

    public function __construct(
        private HttpClientInterface $client,
        #[Autowire('%github_user%')]
        private string $username,
    ) {
    }

    public function getBadges(): BadgeCollection
    {
        $response = $this->client->request('GET', self::HOLOPIN_URL . 'api/stickers', [
            'query' => ['username' => $this->username],
        ]);
        $data = $response->toArray();

        $badges = array_map(fn (array $badge): Badge => new Badge(
            $badge['UserSticker'][0]['id'],
            $badge['name'],
            $badge['description'],
            $badge['image'],
            self::HOLOPIN_URL . 'userbadge/' . $badge['UserSticker'][0]['id'],
            $this->getCategory()
        ), $data['data']['stickers']);

        return new BadgeCollection($badges);
    }

    public function getCategory(): string
    {
        return 'Holopin';
    }
}
