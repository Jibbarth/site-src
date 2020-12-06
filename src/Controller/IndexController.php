<?php

declare(strict_types=1);

namespace App\Controller;

use App\Collection\SliceableCollection;
use App\Constant\ProjectCategory;
use App\Repository\ArticleRepositoryInterface;
use App\Repository\BadgeRepositoryInterface;
use App\Repository\ProjectRepositoryInterface;
use Ramsey\Collection\CollectionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

final class IndexController
{
    private const MAX_ARTICLE_DISPLAY = 5;
    private const MAX_FEATURED_PROJECTS = 3;

    private Environment $twig;

    private ProjectRepositoryInterface $projectRepository;

    private ArticleRepositoryInterface $articleRepository;

    private BadgeRepositoryInterface $badgeRepository;

    public function __construct(
        Environment $twig,
        ProjectRepositoryInterface $projectRepository,
        ArticleRepositoryInterface $articleRepository,
        BadgeRepositoryInterface $badgeRepository
    ) {
        $this->twig = $twig;
        $this->projectRepository = $projectRepository;
        $this->articleRepository = $articleRepository;
        $this->badgeRepository = $badgeRepository;
    }

    /**
     * @Route("/", name="home")
     */
    public function __invoke(): Response
    {
        $featuredProjects = $this->projectRepository->getAll()
            ->where('getCategory', ProjectCategory::FEATURED);
        if ($featuredProjects instanceof SliceableCollection) {
            $featuredProjects = $featuredProjects->slice(0, self::MAX_FEATURED_PROJECTS);
        }

        $articleCollection = $this->articleRepository->getAll()
            ->sort('getDate', CollectionInterface::SORT_DESC);

        if ($articleCollection instanceof SliceableCollection) {
            $articleCollection = $articleCollection->slice(0, self::MAX_ARTICLE_DISPLAY);
        }

        return new Response($this->twig->render('index.html.twig', [
            'projects' => $featuredProjects,
            'articles' => $articleCollection,
            'badges' => $this->badgeRepository->getBadges(),
        ]));
    }
}
