<?php

declare(strict_types=1);

namespace App\Repository\Article;

use App\Collection\ArticleCollection;
use App\Constant\ArticleType;
use App\Model\Article;
use App\Repository\ArticleRepositoryInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ExternalArticleRepository implements ArticleRepositoryInterface
{
    private const FILENAME = 'external_articles.yaml';
    private const CACHE_TTL = 30 * 24 * 3600;

    private ArticleCollection $collection;

    public function __construct(
        private SerializerInterface $serializer,
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
        $this->collection = new ArticleCollection([]);
    }

    public function getAll(): ArticleCollection
    {
        if ($this->collection->isEmpty()) {
            $filename = $this->projectDir . \DIRECTORY_SEPARATOR . 'data' . \DIRECTORY_SEPARATOR . self::FILENAME;

            /** @var array<int, Article> $articles */
            $articles = $this->serializer->deserialize(file_get_contents($filename), Article::class . '[]', 'yaml');

            foreach ($articles as $index => $article) {
                if (ArticleType::EXTERNAL === $article->getType() && '' === $article->getContent()) {
                    $description = $this->fetchMetaDescription($article);

                    if ('' !== $description) {
                        $articles[$index] = $article->withContent($description);
                    }
                }
            }

            $this->collection = new ArticleCollection($articles);
        }

        return $this->collection;
    }

    public function getById(string $id): Article
    {
        if ($this->collection->isEmpty()) {
            $this->getAll();
        }

        $articlesFiltered = $this->collection
            ->filter(static fn (Article $article): bool => $article->getId() === $id);

        if ($articlesFiltered->isEmpty()) {
            throw new NotFoundHttpException();
        }

        return $articlesFiltered->first();
    }

    private function fetchMetaDescription(Article $article): string
    {
        if (null === $article->getUrl()) {
            return '';
        }

        $key = 'external_article_meta_' . md5($article->getUrl());

        try {
            return $this->cache->get($key, function (ItemInterface $item) use ($article): string {
                $item->expiresAfter(self::CACHE_TTL);

                try {
                    $response = $this->httpClient->request('GET', $article->getUrl(), [
                        'timeout' => 3,
                    ]);
                    $html = $response->getContent(false);
                } catch (\Throwable) {
                    return '';
                }

                if ('' === $html) {
                    return '';
                }

                preg_match('/<meta[^>]+(?:name|property)=["\'](?:description|og:description)["\'][^>]*content=["\']([^"\']*)["\']/i', $html, $after);

                if ([] === $after) {
                    preg_match('/<meta[^>]+content=["\']([^"\']*)["\'][^>]*(?:name|property)=["\'](?:description|og:description)["\']/i', $html, $after);
                }

                if ([] === $after) {
                    return '';
                }

                return mb_trim(html_entity_decode($after[1], \ENT_QUOTES));
            });
        } catch (InvalidArgumentException) {
            return '';
        }
    }
}
