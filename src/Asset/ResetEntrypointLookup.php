<?php

declare(strict_types=1);

namespace App\Asset;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

#[AsDecorator(decorates: 'webpack_encore.entrypoint_lookup[_default]')]
final class ResetEntrypointLookup implements EntrypointLookupInterface
{
    public function __construct(private EntrypointLookupInterface $decorated)
    {
    }

    /**
     * @return array<string>
     */
    public function getJavaScriptFiles(string $entryName): array
    {
        $this->decorated->reset();

        return $this->decorated->getJavaScriptFiles($entryName);
    }

    /**
     * @return array<string>
     */
    public function getCssFiles(string $entryName): array
    {
        $this->decorated->reset();

        return $this->decorated->getCssFiles($entryName);
    }

    public function reset(): void
    {
        $this->decorated->reset();
    }
}
