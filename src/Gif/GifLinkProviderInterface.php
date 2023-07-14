<?php

declare(strict_types=1);

namespace App\Gif;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.gif_provider')]
interface GifLinkProviderInterface
{
    public function getRandom(string $search): string;
}
