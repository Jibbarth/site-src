<?php

declare(strict_types=1);

namespace App\Twig;

use App\Model\Article;
use Carbon\Carbon;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class IsOutdatedArticleExtension extends AbstractExtension
{
    public function __construct(
        #[Autowire('%min_diff_to_be_outdated%')]
        private int $minDiffInDayToBeOutdated
    ) {
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
