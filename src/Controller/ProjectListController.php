<?php

declare(strict_types=1);

namespace App\Controller;

use App\Constant\ProjectCategory;
use App\Repository\ProjectRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

final class ProjectListController
{
    public function __construct(
        private Environment $renderer,
        private ProjectRepositoryInterface $repository,
    ) {
    }

    #[Route('/projects', name: 'project_list')]
    public function __invoke(): Response
    {
        $allArticles = $this->repository->getAll();

        return new Response($this->renderer->render('project/list.html.twig', [
            'featured_projects' => $allArticles->where('getCategory', ProjectCategory::FEATURED),
            'old_projects' => $allArticles->where('getCategory', ProjectCategory::OLD_STUFF),
        ]));
    }
}
