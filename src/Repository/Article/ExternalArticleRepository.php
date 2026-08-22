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

final class ExternalArticleRepository implements ArticleRepositoryInterface
{
    private const FILENAME = 'external_articles.yaml';

    private ArticleCollection $collection;

    public function __construct(
        private SerializerInterface $serializer,
        #[Autowire(service: 'cache.app')]
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

                    if (null !== $description) {
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

    /**
     * Fetches the remote meta description, memoized in the Symfony cache
     * (cache.app) so static builds never re-hit third-party sites.
     */
    private function fetchMetaDescription(Article $article): ?string
    {
        if (null === $article->getUrl()) {
            return null;
        }

        $key = 'external_article_meta_' . md5($article->getUrl());

        try {
            return $this->cache->get($key, static function (ItemInterface $item) use ($article): ?string {
                $context = stream_context_create(['http' => [
                    'timeout' => 3,
                    'header' => "User-Agent: Mozilla/5.0 (compatible; jibebarth.fr static builder)\r\n",
                ]]);
                $html = @file_get_contents($article->getUrl(), false, $context);

                if (false === $html || '' === $html) {
                    // ponytail: failures are not cached, one retry per build;
                    // add a negative-TTL entry if a flaky host slows builds
                    return null;
                }

                // ponytail: regex on meta tags instead of DOM crawl — good enough for
                // description/og:description; switch to DomCrawler if extraction misses appear
                preg_match('/<meta[^>]+(?:name|property)=["\'](?:description|og:description)["\'][^>]*content=["\']([^"\']*)["\']/i', $html, $after)
                    || preg_match('/<meta[^>]+content=["\']([^"\']*)["\'][^>]*(?:name|property)=["\'](?:description|og:description)["\']/i', $html, $after);

                $description = isset($after[1]) ? mb_trim(html_entity_decode($after[1], \ENT_QUOTES)) : '';

                if ('' === $description) {
                    return null;
                }

                $item->expiresAfter(30 * 24 * 3600);

                return $description;
            });
        } catch (InvalidArgumentException) {
            return null;
        }
    }
}
