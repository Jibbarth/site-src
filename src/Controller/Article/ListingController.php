<?php

declare(strict_types=1);

namespace App\Controller\Article;

use App\Repository\ArticleRepositoryInterface;
use Ramsey\Collection\AbstractCollection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

final class ListingController
{
    private Environment $renderer;

    private ArticleRepositoryInterface $repository;

    private RequestStack $requestStack;

    public function __construct(
        Environment $renderer,
        ArticleRepositoryInterface $repository,
        RequestStack $requestStack
    ) {
        $this->renderer = $renderer;
        $this->repository = $repository;
        $this->requestStack = $requestStack;
    }

    #[Route('/blog', name: 'article_list', defaults: ['_format' => 'html'], methods: ['GET'])]
    #[Route('/rss.xml', name: 'blog_rss', methods: ['GET'])]
    public function __invoke(): Response
    {
        $format = 'html';
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            throw new \LogicException('No request');
        }

        if ('blog_rss' === $request->get('_route')) {
            $format = 'xml';
        }

        return new Response($this->renderer->render(sprintf('article/list.%s.twig', $format), [
            'articles' => $this->repository->getAll()->sort('getDate', AbstractCollection::SORT_DESC),
        ]));
    }
}
