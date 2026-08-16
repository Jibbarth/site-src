<?php

declare(strict_types=1);

namespace App\StaticSiteBuilder\Command;

use App\Kernel;
use App\StaticSiteBuilder\ControllerWithDataProviderInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

use function Symfony\Component\String\u;

#[AsCommand(
    name: 'dump-static-site',
    description: 'Build the static site',
)]
final class StaticSiteBuilderCommand
{
    private string $outputDirectory;

    /**
     * @param iterable<ControllerWithDataProviderInterface> $controllersWithData
     */
    public function __construct(
        #[AutowireIterator(ControllerWithDataProviderInterface::class)]
        private iterable $controllersWithData,
    ) {
    }

    public function __invoke(
        OutputInterface $output,
        SymfonyStyle $symfonyStyle,
        #[Option(
            description: 'Output directory',
            name: 'output_dir',
            shortcut: 'o',
        )]
        string $outputDirectory = 'output',
    ): int {
        $this->outputDirectory = $outputDirectory;
        $symfonyStyle->title('Building the static site in '.$this->outputDirectory.' directory');

        $kernel = new Kernel('prod', false);
        $kernel->boot();
        /** @var RouterInterface $router */
        $router = $kernel->getContainer()->get('router');
        $routes = $router->getRouteCollection();

        $progress = $this->createProgressBar($symfonyStyle, $routes->count());

        $client = new KernelBrowser($kernel);
        $client->enableReboot();

        [$routesWithoutParam, $routesWithParam] = $this->splitRoutes($routes);

        $status = $this->dumpRoutesWithoutParams($client, $routesWithoutParam, $symfonyStyle, $progress);
        if (Command::FAILURE === $status) {
            return $status;
        }

        $status = $this->dumpRoutesWithParams($client, $router, $routesWithParam, $symfonyStyle, $progress);
        if (Command::FAILURE === $status) {
            return $status;
        }

        $progress->setMessage('✅ Routes processed');
        $progress->finish();
        $symfonyStyle->newLine(2);

        $this->copyAssets($symfonyStyle);

        $symfonyStyle->success('🥳 Static site built successfully!');
        $symfonyStyle->note('Run local server to see the output: "php -S localhost:8001 -t '.$this->outputDirectory.'"');

        return Command::SUCCESS;
    }

    /**
     * @return array{0: array<string, Route>, 1: array<string, Route>}
     */
    private function splitRoutes(RouteCollection $routes): array
    {
        $without = [];
        $with = [];

        foreach ($routes->all() as $name => $route) {
            if (str_contains($route->getPath(), '{')) {
                $with[$name] = $route;
            } else {
                $without[$name] = $route;
            }
        }

        return [$without, $with];
    }

    /**
     * @param array<string, Route>   $routes
     */
    private function dumpRoutesWithoutParams(
        KernelBrowser $client,
        array $routes,
        SymfonyStyle $symfonyStyle,
        \Symfony\Component\Console\Helper\ProgressBar $progress,
    ): int {
        foreach ($routes as $routeName => $route) {
            $progress->setMessage(\sprintf('Processing route %s (%s)', $routeName, $route->getPath()));
            $progress->advance();
            $client->request('GET', $route->getPath());
            if (!$client->getResponse()->isSuccessful()) {
                $symfonyStyle->error(\sprintf('Error processing route %s (%s)', $routeName, $route->getPath()));

                return Command::FAILURE;
            }
            $this->dumpResponse($client->getRequest(), $client->getResponse());
        }

        return Command::SUCCESS;
    }

    /**
     * @param array<string, Route>    $routes
     */
    private function dumpRoutesWithParams(
        KernelBrowser $client,
        RouterInterface $router,
        array $routes,
        SymfonyStyle $symfonyStyle,
        \Symfony\Component\Console\Helper\ProgressBar $progress,
    ): int {
        foreach ($routes as $routeName => $route) {
            $routeController = $this->findControllerForRoute($route);

            if (null === $routeController) {
                $symfonyStyle->writeln(\sprintf('No controller found for route %s', $route->getPath()));
                continue;
            }

            $progress->advance();
            foreach ($routeController->getArguments() as $routeArgument) {
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

        return Command::SUCCESS;
    }

    private function findControllerForRoute(Route $route): ?ControllerWithDataProviderInterface
    {
        foreach ($this->controllersWithData as $controller) {
            if ($controller::class === $route->getDefault('_controller')) {
                return $controller;
            }
        }

        return null;
    }

    private function createProgressBar(SymfonyStyle $symfonyStyle, int $count): \Symfony\Component\Console\Helper\ProgressBar
    {
        $progress = $symfonyStyle->createProgressBar($count);
        $format = $progress::getFormatDefinition('normal');
        $progress::setFormatDefinition('custom', $format.' -- %message%');
        $progress->setFormat('custom');

        return $progress;
    }

    private function copyAssets(SymfonyStyle $symfonyStyle): void
    {
        $symfonyStyle->info('⏳ Copying assets...');
        $fileSystem = new Filesystem();
        $fileSystem->mirror('public', $this->outputDirectory);
        $fileSystem->remove($this->outputDirectory.'/index.php');
        $symfonyStyle->info('✅ Assets copied');
    }

    private function dumpResponse(Request $request, Response $response): void
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
