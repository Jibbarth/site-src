<?php

declare(strict_types=1);

namespace App\Twig;

use App\Model\Photo;
use App\Photo\UnsplashRandomPhotoProvider;
use Symfony\Contracts\Cache\CacheInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class UnsplashExtension extends AbstractExtension
{
    public function __construct(
        private UnsplashRandomPhotoProvider $photoProvider,
        private CacheInterface $cache,
    ) {}

    /**
     * @return array<\Twig\TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('unsplash', [$this, 'getUnsplashImage']),
            new TwigFunction('unsplash_url', [$this, 'getUnsplashUrl']),
            new TwigFunction('unsplash_photo', [$this, 'getUnsplashPhotoObject']),
        ];
    }

    /**
     * Return a base64encoded image
     *
     * @deprecated, use unsplash_photo instead with blurhash as base64 and lazy load full url
     */
    public function getUnsplashImage(string $keyword, int $width = 1600, int $height = 900): string
    {
        $url = $this->getUnsplashUrl($keyword, $width, $height);
        $content = file_get_contents($url);
        if (false === $content) {
            throw new \LogicException('Unable to download ' . $url);
        }

        return 'data:image/png;base64, ' . base64_encode($content);
    }

    public function getUnsplashUrl(string $keyword, int $width = 1600, int $height = 900): string
    {
        $photo = $this->getUnsplashPhotoObject($keyword, $width, $height);

        return $photo->fullUrl;
    }

    public function getUnsplashPhotoObject(string $keyword, int $width = 1600, int $height = 900): Photo
    {
        return $this->cache->get(
            'unsplash_' . $keyword . $width . $height,
            fn () => $this->photoProvider->find($keyword, $width, $height)
        );
    }
}
