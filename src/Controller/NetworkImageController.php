<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symplify\SymfonyStaticDumper\Contract\ControllerWithDataProviderInterface;
use Twig\Environment;

final class NetworkImageController implements ControllerWithDataProviderInterface
{
    private Environment $twig;

    private HttpClientInterface $client;

    public function __construct(
        Environment $twig,
        HttpClientInterface $client,
    ) {
        $this->twig = $twig;
        $this->client = $client;
    }

    #[Route('/img/fa/{type}-{collection}-{size}.svg', name: 'image')]
    public function __invoke(string $type, string $collection, int $size): Response
    {
        $faSvgUrl = sprintf(
            'https://raw.githubusercontent.com/FortAwesome/Font-Awesome/master/svgs/%s/%s.svg',
            $collection,
            $type
        );

        return new Response($this->twig->render('_partials/font_awesome_svg.svg.twig', [
            'size' => $size,
            'fa_svg' => $this->client->request('GET', $faSvgUrl)->getContent(),
        ]), 200, ['Content-Type' => 'image/svg+xml']);
    }

    public function getControllerClass(): string
    {
        return self::class;
    }

    public function getControllerMethod(): string
    {
        return '__invoke';
    }

    /**
     * @return array<array<int|string>>
     */
    public function getArguments(): array
    {
        return [
            ['rss', 'solid', 100],
            ['facebook', 'brands', 100],
            ['github', 'brands', 100],
            ['twitter', 'brands', 100],
            ['symfony', 'brands', 100],
            ['linkedin', 'brands', 100],
        ];
    }
}
