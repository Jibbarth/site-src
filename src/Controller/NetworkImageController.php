<?php

declare(strict_types=1);

namespace App\Controller;

use App\StaticSiteBuilder\ControllerWithDataProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Icons\IconRegistryInterface;
use Twig\Environment;

final class NetworkImageController implements ControllerWithDataProviderInterface
{
    public function __construct(
        private Environment $twig,
        #[Autowire(service: '.ux_icons.icon_registry')]
        private IconRegistryInterface $iconRegistry,
    ) {}

    #[Route('/img/fa/{type}-{collection}.svg', name: 'image')]
    public function __invoke(string $type, string $collection): Response
    {
        $icon = $this->iconRegistry->get(\sprintf('fa-%s:%s', $collection, $type));

        return new Response($this->twig->render('_partials/font_awesome_svg.svg.twig', [
            'icon' => $icon,
        ]), 200, ['Content-Type' => 'image/svg+xml']);
    }

    /**
     * @return array<array<string, string>>
     */
    public function getArguments(): array
    {
        return [
            ['type' => 'rss', 'collection' => 'solid'],
            ['type' => 'facebook', 'collection' => 'brands'],
            ['type' => 'github', 'collection' => 'brands'],
            ['type' => 'twitter', 'collection' => 'brands'],
            ['type' => 'symfony', 'collection' => 'brands'],
            ['type' => 'linkedin', 'collection' => 'brands'],
        ];
    }
}
