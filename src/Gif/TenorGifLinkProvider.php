<?php

declare(strict_types=1);

namespace App\Gif;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class TenorGifLinkProvider implements GifLinkProviderInterface
{
    private const RANDOM_URL = 'https://api.tenor.com/v1/random?key=LIVDSRZULELA&limit=1&q=';

    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getRandom(string $search): string
    {
        $response = $this->client->request('GET', self::RANDOM_URL . $search);

        $imageUrl = $response->toArray()['results'][0]['media'][0]['gif']['url'];
        if (!\is_string($imageUrl)) {
            throw new \LogicException('Unable to find gif url for the search: ' . $search);
        }

        return $imageUrl;
    }
}
