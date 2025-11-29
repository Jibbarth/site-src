<?php

declare(strict_types=1);

namespace App\Command;

use App\Generator\ReadmeGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'generate-readme')]
final class GenerateReadmeCommand
{
    public function __construct(private ReadmeGenerator $readmeGenerator) {}

    public function __invoke(
        SymfonyStyle $style,
    ): int {
        $this->readmeGenerator->generate();
        $style->success('Readme successfully generated');

        return Command::SUCCESS;
    }
}
