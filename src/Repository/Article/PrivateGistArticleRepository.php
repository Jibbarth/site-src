<?php

declare(strict_types=1);

namespace App\Repository\Article;

use App\Collection\ArticleCollection;
use App\Gist\ArticleFromGistLoader;
use App\Model\Article;
use App\Repository\ArticleRepositoryInterface;
use Symfony\Component\Yaml\Yaml;

final class PrivateGistArticleRepository implements ArticleRepositoryInterface
{
    private const FILENAME = 'private_gists.yaml';

    private ArticleFromGistLoader $gistArticleLoader;

    private string $projectDir;

    private Yaml $yaml;

    public function __construct(ArticleFromGistLoader $gistArticleLoader, string $projectDir)
    {
        $this->projectDir = $projectDir;
        $this->gistArticleLoader = $gistArticleLoader;
        $this->yaml = new Yaml();
    }

    public function getById(string $id): Article
    {
        return $this->gistArticleLoader->retrieve($id);
    }

    public function getAll(): ArticleCollection
    {
        $filename = $this->projectDir . \DIRECTORY_SEPARATOR . 'data' . \DIRECTORY_SEPARATOR . self::FILENAME;
        $listIds = $this->yaml::parse(\Safe\file_get_contents($filename));

        $articles = [];
        foreach ($listIds as $id) {
            $articles[] = $this->gistArticleLoader->retrieve($id);
        }

        return new ArticleCollection($articles);
    }
}
