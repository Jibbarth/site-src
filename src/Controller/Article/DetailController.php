<?php

declare(strict_types=1);

namespace App\Controller\Article;

use App\Constant\ArticleType;
use App\Repository\ArticleRepositoryInterface;
use App\StaticSiteBuilder\ControllerWithDataProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

final class DetailController implements ControllerWithDataProviderInterface
{
    public function __construct(
        private Environment $renderer,
        private ArticleRepositoryInterface $articleRepository,
    ) {}

    #[Route('/{type}/{article}', name: 'article_detail')]
    public function __invoke(string $type, string $article): Response
    {
        $post = $this->articleRepository->getById($article);

        return new Response($this->renderer->render(\Safe\sprintf('article/%s.html.twig', $type), [
            'article' => $post,
        ]));
    }

    /**
     * @return array<array<string, string>>
     */
    public function getArguments(): array
    {
        $collection = $this->articleRepository->getAll();

        $arguments = [];

        /** @var \App\Model\Article $article */
        foreach ($collection as $article) {
            if (ArticleType::EXTERNAL === $article->getType()) {
                continue;
            }
            $arguments[] = ['type' => $article->getType(), 'article' => $article->getId()];
        }

        return [...$arguments];
    }
}
