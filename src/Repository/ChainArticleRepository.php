<?php

declare(strict_types=1);

namespace App\Repository;

use App\Collection\ArticleCollection;
use App\Model\Article;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ChainArticleRepository implements ArticleRepositoryInterface
{
    /**
     * @var array<ArticleRepositoryInterface>
     */
    private array $repositories = [];
    private string $env;

    /**
     * @param iterable<\App\Repository\ArticleRepositoryInterface> $articleRepositories
     */
    public function __construct(iterable $articleRepositories, string $env)
    {
        foreach ($articleRepositories as $articleRepository) {
            if ($articleRepository instanceof self) {
                continue;
            }

            $this->repositories[] = $articleRepository;
        }
        $this->env = $env;
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
