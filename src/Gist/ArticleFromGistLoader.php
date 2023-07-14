<?php

declare(strict_types=1);

namespace App\Gist;

use App\Model\Article;
use Github\Client;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class ArticleFromGistLoader
{
    public function __construct(private CacheInterface $cache, private Client $client)
    {
    }

    public function retrieve(string $gistId): Article
    {
        $gist = $this->cache->get(
            'gist_' . $gistId,
            function (ItemInterface $item) use ($gistId): array {
                $item->expiresAfter(3600);

                return $this->client->gist()->show($gistId);
            }
        );

        $file = array_shift($gist['files']);
        $article = new Article(
            $gist['id'],
            $gist['description'],
            $file['content'],
            new \DateTime($gist['created_at']),
            $gist['html_url']
        );

        return $article->withFiles($gist['files']);
    }
}
