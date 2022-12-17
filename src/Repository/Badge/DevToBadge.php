<?php

declare(strict_types=1);

namespace App\Repository\Badge;

use App\Collection\BadgeCollection;
use App\Model\Badge;
use App\Repository\BadgeRepositoryInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class DevToBadge implements BadgeRepositoryInterface
{
    private const DEV_TO_URL = 'https://dev.to/';

    private const BADGE_NODE_PATH = '.js-profile-badges .js-profile-badge';

    public function __construct(
        private HttpClientInterface $client,
        private string $githubUser
    ) {
    }

    public function getBadges(): BadgeCollection
    {
        $uri = sprintf('%s%s', self::DEV_TO_URL, $this->githubUser);

        $response = $this->client->request('GET', $uri);

        $crawler = new Crawler($response->getContent(), $uri, self::DEV_TO_URL);
        $badges = $crawler
            ->filter(self::BADGE_NODE_PATH)
            ->each(fn (Crawler $node) => new Badge(
                uniqid('', true),
                $node->attr('title') ?? '',
                $node->nextAll()->filter('.hidden')->first()->filter('p.description')->text(''),
                $node->filter('img')->attr('src') ?? '',
                $uri,
                $this->getCategory(),
            ));

        return new BadgeCollection($badges);
    }

    public function getCategory(): string
    {
        return 'DevTo';
    }
}
