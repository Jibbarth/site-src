<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class UnsplashExtension extends AbstractExtension
{
    private const UNSPLASH_PATTERN = 'https://source.unsplash.com/%sx%s/?%s';

    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @return array<\Twig\TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('unsplash', [$this, 'getUnsplashImage']),
            new TwigFunction('unsplash_url', [$this, 'getUnsplashUrl']),
        ];
    }

    /**
     * Return a base64encoded image
     */
    public function getUnsplashImage(string $keyword, int $width = 1600, int $height = 900): string
    {
        $response = $this->client->request(
            'GET',
            \Safe\sprintf(self::UNSPLASH_PATTERN, $width, $height, $keyword)
        );

        return 'data:image/png;base64, ' . base64_encode($response->getContent());
    }

    public function getUnsplashUrl(string $keyword, int $width = 1600, int $height = 900): string
    {
        $response = $this->client->request(
            'GET',
            \Safe\sprintf(self::UNSPLASH_PATTERN, $width, $height, $keyword)
        );

        $response->getStatusCode(); // Retrieve info

        return $response->getInfo()['url'];
    }
}
