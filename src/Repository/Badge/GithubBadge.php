<?php

declare(strict_types=1);

namespace App\Repository\Badge;

use App\Collection\BadgeCollection;
use App\Model\Badge;
use App\Repository\BadgeRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GithubBadge implements BadgeRepositoryInterface
{
    private const GITHUB_URL = 'https://github.com';
    private const TAB_QUERY_PARAM = '?tab=achievements';
    private const ACHIEVEMENT_QUERY_PARAM = '&achievement=';
    private const BADGE_NODE_PATH = '.js-achievement-card-details';

    public function __construct(
        private HttpClientInterface $client,
        #[Autowire('%github_user%')]
        private string $githubUser
    ) {
    }

    public function getBadges(): BadgeCollection
    {
        $baseUrl = sprintf('%s/%s%s', self::GITHUB_URL, $this->githubUser, self::TAB_QUERY_PARAM);
        $response = $this->client->request('GET', $baseUrl);

        $crawler = new Crawler($response->getContent(), self::GITHUB_URL, self::GITHUB_URL);
        $badges = $crawler
            ->filter(self::BADGE_NODE_PATH)

            ->each(function (Crawler $node) use ($baseUrl) {
                return new Badge(
                    uniqid('', true),
                    $node->filter('h3')->innerText(),
                    '',
                    $node->filter('img')->attr('src') ?? '',
                    $baseUrl . self::ACHIEVEMENT_QUERY_PARAM . $node->attr('data-achievement-slug'),
                    $this->getCategory(),
                );
            });

        return new BadgeCollection($badges);
    }

    public function getCategory(): string
    {
        return 'Github';
    }
}
