<?php

declare(strict_types=1);

namespace App\StaticSiteBuilder;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface ControllerWithDataProviderInterface
{
    public function getArguments(): array;
}
