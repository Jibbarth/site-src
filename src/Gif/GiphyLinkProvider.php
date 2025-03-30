<?php

declare(strict_types=1);

namespace App\Gif;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GiphyLinkProvider implements GifLinkProviderInterface
{
    // API KEY is the public api_key for tests
    private const RANDOM_URL = 'https://api.giphy.com/v1/gifs/random?api_key=dc6zaTOxFJmzC&tag=';

    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getRandom(string $search): string
    {
        $response = $this->client->request('GET', self::RANDOM_URL . $search);

        $imageUrl = $response->toArray()['data']['image_url'];
        if (!\is_string($imageUrl)) {
            throw new \LogicException('Unable to find gif url for the search: ' . $search);
        }

        return $imageUrl;
    }
}
