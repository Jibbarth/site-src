<?php

declare(strict_types=1);

namespace App\Command;

use App\Generator\ReadmeGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'generate-readme')]
final class GenerateReadmeCommand extends Command
{
    public function __construct(private ReadmeGenerator $readmeGenerator)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $this->readmeGenerator->generate();
        $style->success('Readme successfully generated');

        return self::SUCCESS;
    }
}
