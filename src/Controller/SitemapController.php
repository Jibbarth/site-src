<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArticleRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symplify\SymfonyStaticDumper\Routing\RoutesProvider;
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
        private RoutesProvider $routesProvider,
        private ArticleRepositoryInterface $articleRepository,
        private Environment $renderer,
        private string $websiteUrl,
    ) {
    }

    /**
     * @Route("/sitemap.xml", name="sitemap", defaults={"_format"="xml"})
     */
    public function __invoke(): Response
    {
        $urls = [];
        foreach ($this->routesProvider->provideRoutesWithoutArguments() as $routeName => $route) {
            if (u($routeName)->containsAny(self::$toExclude)) {
                continue;
            }
            $urls[] = ['loc' => $this->websiteUrl . $this->router->generate($routeName)];
        }

        /** @var \App\Model\Article $article */
        foreach ($this->articleRepository->getAll() as $article) {
            if ('external' === $article->getType()) {
                continue;
            }

            $urls[] = [
                'loc' => $this->websiteUrl . $this->router->generate('article_detail', [
                    'type' => $article->getType(),
                    'article' => $article->getId(),
                ]),
                'lastmod' => $article->getDate()->format('Y-m-d'),
            ];
        }

        return new Response($this->renderer->render('sitemap/index.xml.twig', [
            'urls' => $urls,
        ]));
    }
}
