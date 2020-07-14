<?php

declare(strict_types=1);

namespace App\Command;

use App\Generator\ReadmeGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class GenerateReadmeCommand extends Command
{
    private const NAME = 'generate-readme';

    private ReadmeGenerator $readmeGenerator;

    public function __construct(ReadmeGenerator $readmeGenerator)
    {
        parent::__construct(self::NAME);
        $this->readmeGenerator = $readmeGenerator;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $this->readmeGenerator->generate();
        $style->success('Readme successfuly generated');

        return self::SUCCESS;
    }
}
