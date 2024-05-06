<?php

declare(strict_types=1);

namespace App\StaticSiteBuilder;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface ControllerWithDataProviderInterface
{
    /**
     * @return array<array<string, string>>
     */
    public function getArguments(): array;
}
