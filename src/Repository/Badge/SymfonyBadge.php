<?php

declare(strict_types=1);

namespace App\Repository\Badge;

use App\Collection\BadgeCollection;
use App\Model\Badge;
use App\Parser\VndComSymfonyConnectXmlParser;
use App\Repository\BadgeRepositoryInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use SymfonyCorp\Connect\Api\Api;
use SymfonyCorp\Connect\Api\Entity\Badge as SfBadge;
use function Symfony\Component\String\u;

final class SymfonyBadge implements BadgeRepositoryInterface
{
    private Api $api;

    private HttpClientInterface $client;

    private string $userUuid;

    public function __construct(
        HttpClientInterface $client,
        VndComSymfonyConnectXmlParser $parser,
        string $sfConnectUser
    ) {
        $this->api = new Api(null, $client, $parser);
        $this->client = $client;
        $this->userUuid = $sfConnectUser;
    }

    public function getBadges(): BadgeCollection
    {
        $root = $this->api->getRoot();
        $user = $root->getUser($this->userUuid);
        $badges = $user->getBadges();
        $badges = array_map(function (SfBadge $badge) use ($root): Badge {
            /** @var SfBadge $badge */
            $badge = $root->getBadge($badge->getId());
            $badge->setImage($this->resolveImage($badge->getImage()));

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

    private function resolveImage(string $url): string
    {
        $response = $this->client->request('GET', $url);
        $response->getContent();

        $imageUrl = $response->getInfo('url') ?? $url;
        if (!\is_string($imageUrl)) {
            throw new \RuntimeException(sprintf('Unable to resolve image url: %s', $url));
        }

        return u($imageUrl)->replace('30x30', '200x200')->toString();
    }
}
