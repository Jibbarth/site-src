<?php

declare(strict_types=1);

namespace App\Repository\Badge;

use App\Collection\BadgeCollection;
use App\Model\Badge;
use App\Repository\BadgeRepositoryInterface;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function Symfony\Component\String\u;

final class AfupBadge implements BadgeRepositoryInterface
{
    private const AFUP_URL_CONNEXION = 'https://afup.org/admin/login';

    private const BADGE_NODE_PATH = '.container .member-badge img';

    public function __construct(
        private HttpClientInterface $client,
        private string $afupEmail,
        private string $afupPassword
    ) {
    }

    public function getBadges(): BadgeCollection
    {
        $browser = new HttpBrowser($this->client);
        $browser->request('GET', self::AFUP_URL_CONNEXION);

        $crawler = $browser->submitForm('Se connecter', [
            'utilisateur' => $this->afupEmail,
            'mot_de_passe' => $this->afupPassword,
        ]);

        $badges = $crawler
            ->filter(self::BADGE_NODE_PATH)
            ->each(function (Crawler $node): Badge {
                $image = $node->image();
                $title = u($node->attr('alt'))->replace('Badge ', '')->toString();

                return new Badge(
                    uniqid('', true),
                    $title,
                    'Afup : ' . $title,
                    $image->getUri(),
                    $image->getUri(),
                    $this->getCategory(),
                );
            });

        return new BadgeCollection($badges);
    }

    public function getCategory(): string
    {
        return 'Afup';
    }
}
