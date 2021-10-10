<?php

declare(strict_types=1);

namespace App\Twig;

use Carbon\Carbon;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class TimeDiffExtension extends AbstractExtension
{
    /**
     * @return array<\Twig\TwigFilter>
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('time_diff', [$this, 'diff']),
        ];
    }

    public function diff(\DateTimeInterface $date): string
    {
        return (new Carbon($date))->diffForHumans(['parts' => 2, 'join' => true]);
    }
}
