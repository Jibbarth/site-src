<?php

declare(strict_types=1);

namespace App\Generator;

use App\Collection\SliceableCollection;
use App\Constant\ProjectCategory;
use App\Github\LastPrRetriever;
use App\Github\LastStarRetriever;
use App\Repository\ArticleRepositoryInterface;
use App\Repository\ProjectRepositoryInterface;
use Ramsey\Collection\CollectionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

final class ReadmeGenerator
{
    private const FILENAME = 'output/README.md';

    private Environment $twig;

    private ProjectRepositoryInterface $projectRepository;

    private ArticleRepositoryInterface $articleRepository;

    private LastPrRetriever $lastPrRetriever;

    private LastStarRetriever $lastStarRetriever;

    public function __construct(
        Environment $twig,
        ProjectRepositoryInterface $projectRepository,
        ArticleRepositoryInterface $articleRepository,
        LastPrRetriever $lastPrRetriever,
        LastStarRetriever $lastStarRetriever
    ) {
        $this->twig = $twig;
        $this->projectRepository = $projectRepository;
        $this->articleRepository = $articleRepository;
        $this->lastPrRetriever = $lastPrRetriever;
        $this->lastStarRetriever = $lastStarRetriever;
    }

    public function generate(): void
    {
        $featuredProjects = $this->projectRepository->getAll()
            ->where('getCategory', ProjectCategory::FEATURED);
        if ($featuredProjects instanceof SliceableCollection) {
            $featuredProjects = $featuredProjects->slice(0, 3);
        }
        $latestPosts = $this->articleRepository->getAll()
            ->sort('getDate', CollectionInterface::SORT_DESC);

        if ($latestPosts instanceof SliceableCollection) {
            $latestPosts = $latestPosts->slice(0, 2);
        }

        $readmeContent = $this->twig->render('readme.md.twig', [
            'oss_projects' => $featuredProjects,
            'articles' => $latestPosts,
            'pull_requests' => $this->lastPrRetriever->get(),
            'stars' => $this->lastStarRetriever->get(5),
        ]);

        $finder = new Filesystem();
        $finder->dumpFile(self::FILENAME, $readmeContent);
    }
}
