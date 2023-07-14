<?php

declare(strict_types=1);

namespace App\Repository\Article;

use App\Collection\ArticleCollection;
use App\Gist\ArticleFromGistLoader;
use App\Model\Article;
use App\Repository\ArticleRepositoryInterface;
use Github\Client;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class PublicGistArticleRepository implements ArticleRepositoryInterface
{
    public function __construct(
        private Client $client,
        private CacheInterface $cache,
        private ArticleFromGistLoader $gistArticleLoader,
        #[Autowire('%github_user%')]
        private string $githubUser
    ) {
    }

    public function getAll(): ArticleCollection
    {
        $gists = $this->cache->get('gist_list', function (ItemInterface $item): array {
            $item->expiresAfter(3600);

            return $this->client->user()->gists($this->githubUser);
        });

        $articles = array_map(function (array $githubData): Article {
            return $this->gistArticleLoader->retrieve($githubData['id']);
        }, array_filter($gists, static function (array $githubData): bool {
            if (0 === \count($githubData['files'])) {
                return false;
            }
            $file = array_shift($githubData['files']);
            // if first file is not in markdown, consider gist as not relevant

            return 'Markdown' === $file['language'] || 'text/markdown' === $file['type'];
        }));

        return new ArticleCollection($articles);
    }

    public function getById(string $id): Article
    {
        return $this->gistArticleLoader->retrieve($id);
    }
}
