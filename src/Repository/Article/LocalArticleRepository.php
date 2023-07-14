<?php

declare(strict_types=1);

namespace App\Repository\Article;

use App\Collection\ArticleCollection;
use App\Constant\ArticleStatus;
use App\Model\Article;
use App\Parser\FrontYamlParser;
use App\Repository\ArticleRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class LocalArticleRepository implements ArticleRepositoryInterface
{
    private const POST_FOLDER = 'data/_posts/';

    private ArticleCollection $collection;

    public function __construct(
        private FrontYamlParser $parser,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir
    ) {
        $this->collection = new ArticleCollection([]);
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

    public function getAll(): ArticleCollection
    {
        if (!$this->collection->isEmpty()) {
            return $this->collection;
        }

        $finder = (new Finder())->in($this->projectDir . \DIRECTORY_SEPARATOR . self::POST_FOLDER)
            ->files();

        foreach ($finder as $file) {
            $this->collection->add($this->articleFromFile($file));
        }

        return $this->collection;
    }

    private function articleFromFile(SplFileInfo $file): Article
    {
        $document = $this->parser->parse($file->getContents());
        $article = new Article(
            $file->getFilenameWithoutExtension(),
            $document->title,
            $document->content,
            $document->date,
            null,
        );

        if (null !== $document->image) {
            $article->withImage($document->image);
        }
        if (ArticleStatus::DRAFT === $document->state) {
            $article->markInDraft();
        }

        return $article;
    }
}
