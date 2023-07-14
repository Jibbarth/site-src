<?php

declare(strict_types=1);

namespace App\Repository;

use App\Collection\BadgeCollection;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.badge_repository')]
interface BadgeRepositoryInterface
{
    public function getBadges(): BadgeCollection;

    public function getCategory(): string;
}
