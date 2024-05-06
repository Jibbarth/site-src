<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArticleRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

use function Symfony\Component\String\u;

final class SitemapController
{
    /**
     * @var array<string>
     */
    private static array $toExclude = ['blog_rss', 'not_found', '_profiler', 'sitemap'];

    public function __construct(
        private RouterInterface $router,
        private ArticleRepositoryInterface $articleRepository,
        private Environment $renderer,
        #[Autowire('%website_url%')]
        private string $websiteUrl,
    ) {}

    #[Route('/sitemap.xml', name: 'sitemap', defaults: ['_format' => 'xml'])]
    public function __invoke(): Response
    {
        $urls = [];

        /** @var array<\Symfony\Component\Routing\Route> $routesWithoutParam */
        $routesWithoutParam = array_filter($this->router->getRouteCollection()->all(), static fn ($route) => !str_contains($route->getPath(), '{'));

        foreach (array_keys($routesWithoutParam) as $routeName) {
            if (u($routeName)->containsAny(self::$toExclude)) {
                continue;
            }
            $urls[] = ['loc' => $this->websiteUrl . u($this->router->generate($routeName))->ensureEnd('/')->toString()];
        }

        /** @var \App\Model\Article $article */
        foreach ($this->articleRepository->getAll() as $article) {
            if ('external' === $article->getType()) {
                continue;
            }

            $urls[] = [
                'loc' => $this->websiteUrl . u($this->router->generate('article_detail', [
                    'type' => $article->getType(),
                    'article' => $article->getId(),
                ]))->ensureEnd('/')->toString(),
                'lastmod' => $article->getDate()->format('Y-m-d'),
            ];
        }

        return new Response($this->renderer->render('sitemap/index.xml.twig', [
            'urls' => $urls,
        ]));
    }
}
