<?php

declare(strict_types=1);

namespace App\Twig;

use App\Model\Article;
use Carbon\Carbon;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class IsOutdatedArticleExtension extends AbstractExtension
{
    private int $minDiffInDayToBeOutdated;

    public function __construct(int $minDiffInDayToBeOutdated)
    {
        $this->minDiffInDayToBeOutdated = $minDiffInDayToBeOutdated;
    }

    /**
     * @return array<\Twig\TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_outdated_article', [$this, 'isOutdated']),
        ];
    }

    public function isOutDated(Article $article): bool
    {
        $articleDate = new Carbon($article->getDate());

        $diff = Carbon::now()->diffInDays($articleDate);

        return $diff >= $this->minDiffInDayToBeOutdated;
    }
}
