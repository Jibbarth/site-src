<?php

declare(strict_types=1);

namespace App\Repository;

use App\Collection\BadgeCollection;

interface BadgeRepositoryInterface
{
    public function getBadges(): BadgeCollection;

    public function getCategory(): string;
}
