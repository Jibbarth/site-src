<?php

declare(strict_types=1);

namespace App\Repository;

use App\Collection\ArticleCollection;
use App\Model\Article;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.article_repository')]
interface ArticleRepositoryInterface
{
    public function getById(string $id): Article;

    public function getAll(): ArticleCollection;
}
