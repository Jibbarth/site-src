<?php

declare(strict_types=1);

namespace App\StaticSiteBuilder\Command;

use App\Kernel;
use App\StaticSiteBuilder\ControllerWithDataProviderInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

use function Symfony\Component\String\u;

#[AsCommand(
    name: 'dump-static-site',
    description: 'Build the static site',
)]
final class StaticSiteBuilderCommand extends Command
{
    private string $outputDirectory;

    /**
     * @param iterable<ControllerWithDataProviderInterface> $controllersWithData
     */
    public function __construct(
        #[TaggedIterator(ControllerWithDataProviderInterface::class)]
        private iterable $controllersWithData,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('output_dir', 'o', InputOption::VALUE_REQUIRED, 'Output directory', 'output');
    }

    /**
     * @suppressWarnings(PHPMD.CyclomaticComplexity)
     * @suppressWarnings(PHPMD.ExcessiveMethodLength)
     * TODO: refactor this method
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $outputDirectory = $input->getOption('output_dir');
        if (!\is_string($outputDirectory)) {
            $output->writeln('Output directory must be a string');

            return Command::FAILURE;
        }
        $this->outputDirectory = $outputDirectory;

        $symfonyStyle->title('Building the static site in ' . $this->outputDirectory . ' directory');

        // Load a prod kernel
        $kernel = new Kernel('prod', false);
        $kernel->boot();
        /** @var RouterInterface $router */
        $router = $kernel->getContainer()->get('router');
        $routesCollection = $router->getRouteCollection();

        $progress = $symfonyStyle->createProgressBar($routesCollection->count());
        $format = $progress::getFormatDefinition('normal');

        $progress::setFormatDefinition('custom', $format . ' -- %message%');
        $progress->setFormat('custom');

        /** @var array<Route> $routesWithoutParam */
        $routesWithoutParam = array_filter(
            $routesCollection->all(),
            static fn ($route) => !str_contains($route->getPath(), '{')
        );
        /** @var array<Route> $routesWithParam */
        $routesWithParam = array_filter(
            $routesCollection->all(),
            static fn ($route) => str_contains($route->getPath(), '{')
        );

        $client = new KernelBrowser($kernel);
        $client->enableReboot();

        foreach ($routesWithoutParam as $routeName => $route) {
            $progress->setMessage(\sprintf('Processing route %s (%s)', $routeName, $route->getPath()));
            $progress->advance();
            $client->request('GET', $route->getPath());
            if (!$client->getResponse()->isSuccessful()) {
                $symfonyStyle->error(\sprintf('Error processing route %s (%s)', $routeName, $route->getPath()));

                return Command::FAILURE;
            }
            $this->dumpResponse($client->getRequest(), $client->getResponse());
        }

        foreach ($routesWithParam as $routeName => $route) {
            $routeController = null;
            foreach ($this->controllersWithData as $controller) {
                if ($controller::class !== $route->getDefault('_controller')) {
                    continue;
                }

                $routeController = $controller;
            }
            if (null === $routeController) {
                $output->writeln(\sprintf('No controller found for route %s', $route->getPath()));
                continue;
            }

            $arguments = $routeController->getArguments();
            $progress->advance();
            foreach ($arguments as $routeArgument) {
                $progress->setMessage(\sprintf(
                    'Processing route %s (%s) with arguments (%s)',
                    $routeName,
                    $route->getPath(),
                    implode(', ', $routeArgument)
                ));
                $progress->display();
                $client->request('GET', $router->generate($routeName, $routeArgument));
                if (!$client->getResponse()->isSuccessful()) {
                    $symfonyStyle->error(\sprintf(
                        'Error processing route %s (%s) with arguments (%s)',
                        $routeName,
                        $route->getPath(),
                        implode(', ', $routeArgument)
                    ));

                    return Command::FAILURE;
                }
                $this->dumpResponse($client->getRequest(), $client->getResponse());
            }
        }

        $progress->setMessage('✅ Routes processed');
        $progress->finish();
        $symfonyStyle->newLine(2);

        $symfonyStyle->info('⏳ Copying assets...');
        // Copy Assets
        $fileSystem = new Filesystem();
        $fileSystem->mirror('public', $this->outputDirectory);
        // Clean index file
        $fileSystem->remove($this->outputDirectory . '/index.php');

        $symfonyStyle->info('✅ Assets copied');

        $symfonyStyle->success('🥳 Static site built successfully!');
        $symfonyStyle->note('Run local server to see the output: "php -S localhost:8001 -t ' . $this->outputDirectory . '"');

        // Build the static site
        return Command::SUCCESS;
    }

    private function dumpResponse(Request $request, \Symfony\Component\HttpFoundation\Response $response): void
    {
        $content = $response->getContent();
        if (false === $content) {
            return;
        }

        $filename = 'index.html';
        $folder = $request->getPathInfo();
        if ([] !== u($request->getPathInfo())->match('#\.[\w]+#')) {
            $filename = u($request->getPathInfo())->afterLast('/')->toString();
            $folder = u($request->getPathInfo())->beforeLast('/')->toString();
        }

        $fileSystem = new Filesystem();
        $fileSystem->dumpFile(
            \sprintf(
                '%s%s%s',
                $this->outputDirectory,
                u($folder)->ensureStart('/')->ensureEnd('/')->toString(),
                $filename
            ),
            $content
        );
    }
}
