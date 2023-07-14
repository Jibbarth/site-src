<?php

declare(strict_types=1);

namespace App\Repository\Badge;

use App\Collection\BadgeCollection;
use App\Model\Badge;
use App\Parser\VndComSymfonyConnectXmlParser;
use App\Repository\BadgeRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use SymfonyCorp\Connect\Api\Api;
use SymfonyCorp\Connect\Api\Entity\Badge as SfBadge;

final class SymfonyBadge implements BadgeRepositoryInterface
{
    private Api $api;

    public function __construct(
        private HttpClientInterface $client,
        #[Autowire('%sf_connect_uuid%')]
        private string $userUuid,
        VndComSymfonyConnectXmlParser $parser,
    ) {
        $this->api = new Api(null, $client, $parser);
    }

    public function getBadges(): BadgeCollection
    {
        $root = $this->api->getRoot();
        $user = $root->getUser($this->userUuid);
        $badges = $user->getBadges();
        $badgeImages = $this->resolveBadgeImages($badges->getItems());

        $badges = array_map(function (SfBadge $badge) use ($root, $badgeImages): Badge {
            /** @var SfBadge $badge */
            $badge = $root->getBadge($badge->getId());
            $badge->setImage($badgeImages[$badge->getId()]);

            return new Badge(
                (string) $badge->getId(),
                $badge->getName(),
                $badge->getDescription(),
                $badge->getImage(),
                $badge->getAlternateUrl(),
                $this->getCategory()
            );
        }, $badges->getItems());

        return new BadgeCollection($badges);
    }

    public function getCategory(): string
    {
        return 'SymfonyConnect';
    }

    /**
     * @param array<SfBadge> $badges
     *
     * @return array<int, string>
     */
    private function resolveBadgeImages(array $badges): array
    {
        $responses = [];
        foreach ($badges as $badge) {
            $responses[$badge->getId()] = $this->client->request('GET', $badge->getImage());
        }

        $return = [];
        foreach ($responses as $id => $response) {
            $response->getHeaders();
            $imageUrl = $response->getInfo('url');
            if (!\is_string($imageUrl)) {
                throw new \RuntimeException('Unable to resolve image url');
            }
            $return[$id] = $imageUrl;
        }

        return $return;
    }
}
