<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Icons\IconRegistryInterface;
use Symplify\SymfonyStaticDumper\Contract\ControllerWithDataProviderInterface;
use Twig\Environment;

final class NetworkImageController implements ControllerWithDataProviderInterface
{
    public function __construct(
        private Environment $twig,
        #[Autowire(service: '.ux_icons.icon_registry')]
        private IconRegistryInterface $iconRegistry
    ) {}

    #[Route('/img/fa/{type}-{collection}-{size}.svg', name: 'image')]
    public function __invoke(string $type, string $collection, int $size): Response
    {
        $icon = $this->iconRegistry->get(sprintf('fa-%s:%s', $collection, $type));

        return new Response($this->twig->render('_partials/font_awesome_svg.svg.twig', [
            'size' => $size,
            'icon' => $icon,
            'fa_ux_icon' => sprintf('fa-%s:%s', $collection, $type),
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
