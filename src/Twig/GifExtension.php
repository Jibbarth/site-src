<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class GifExtension extends AbstractExtension
{
    private const BAD_KEYWORDS = ['lost', 'sad'];

    /**
     * @param iterable<\App\Gif\GifLinkProviderInterface> $gifProviders
     */
    public function __construct(
        #[TaggedIterator('app.gif_provider')]
        private iterable $gifProviders
    ) {
    }

    /**
     * @return array<\Twig\TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('gif', [$this, 'getRandomGifLink']),
        ];
    }

    public function getRandomGifLink(string $searchTag): string
    {
        /** @var \App\Gif\GifLinkProviderInterface $gifProvider */
        foreach ($this->gifProviders as $gifProvider) {
            try {
                return $gifProvider->getRandom($searchTag);
            } catch (\Throwable $throwable) {
                continue;
            }
        }

        return $this->getOpiniatedFallbackGiphyLink($searchTag);
    }

    private function getOpiniatedFallbackGiphyLink(string $searchTag): string
    {
        if (\in_array($searchTag, self::BAD_KEYWORDS, true)) {
            return 'https://media.giphy.com/media/Bp3dFfoqpCKFyXuSzP/giphy.gif';
        }

        return 'https://media.giphy.com/media/yoJC2GnSClbPOkV0eA/giphy.gif';
    }
}
