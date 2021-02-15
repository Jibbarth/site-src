<?php

declare(strict_types=1);

namespace App\Collection;

use App\Model\Article;
use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<Article>
 */
final class ArticleCollection extends AbstractCollection implements SliceableCollection
{
    use SliceTrait;

    public function getType(): string
    {
        return Article::class;
    }
}
