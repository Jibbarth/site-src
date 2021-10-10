<?php

declare(strict_types=1);

namespace App\Repository\Article;

use App\Collection\ArticleCollection;
use App\Model\Article;
use App\Repository\ArticleRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

final class ExternalArticleRepository implements ArticleRepositoryInterface
{
    private const FILENAME = 'external_articles.yaml';

    private SerializerInterface $serializer;

    private ArticleCollection $collection;

    private string $projectDir;

    public function __construct(SerializerInterface $serializer, string $projectDir)
    {
        $this->serializer = $serializer;
        $this->projectDir = $projectDir;
        $this->collection = new ArticleCollection([]);
    }

    public function getAll(): ArticleCollection
    {
        if ($this->collection->isEmpty()) {
            $filename = $this->projectDir . \DIRECTORY_SEPARATOR . 'data' . \DIRECTORY_SEPARATOR . self::FILENAME;

            $articles = $this->serializer->deserialize(file_get_contents($filename), Article::class . '[]', 'yaml');

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
}
