<?php

declare(strict_types=1);

namespace App\Repository;

use App\Collection\ArticleCollection;
use App\Model\Article;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsAlias(ArticleRepositoryInterface::class)]
final class ChainArticleRepository implements ArticleRepositoryInterface
{
    /**
     * @param iterable<\App\Repository\ArticleRepositoryInterface> $repositories
     */
    public function __construct(
        #[TaggedIterator('app.article_repository')]
        private iterable $repositories,
        #[Autowire('%kernel.environment%')]
        private string $env
    ) {
    }

    public function getAll(): ArticleCollection
    {
        $articles = new ArticleCollection();
        $allArticles = [];

        foreach ($this->repositories as $repository) {
            $allArticles[] = $repository->getAll();
        }

        $articles = $articles->merge(...$allArticles);
        if ('prod' === $this->env) {
            $articles = $articles->filter(static fn (Article $article): bool => $article->isPublished());
        }
        \assert($articles instanceof ArticleCollection);

        return $articles;
    }

    public function getById(string $id): Article
    {
        foreach ($this->repositories as $repository) {
            try {
                return $repository->getById($id);
            } catch (NotFoundHttpException $exception) {
                continue;
            }
        }

        throw new NotFoundHttpException('Unable to find article with id' . $id);
    }
}
