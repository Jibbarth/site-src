<?php

declare(strict_types=1);

namespace App\Repository;

use App\Collection\ProjectCollection;
use App\Model\Project;
use InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Yaml\Yaml;

final class FileProjectRepository implements ProjectRepositoryInterface
{
    private const FILENAME = 'projects.yaml';

    private ProjectCollection $projects;

    private SluggerInterface $slugger;

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(SerializerInterface $serializer, SluggerInterface $slugger, string $projectDir)
    {
        $filename = $projectDir . \DIRECTORY_SEPARATOR . 'data' . \DIRECTORY_SEPARATOR . self::FILENAME;
        $this->slugger = $slugger;

        $data = Yaml::parse(\Safe\file_get_contents($filename), Yaml::PARSE_CONSTANT);
        \assert($serializer instanceof DenormalizerInterface);
        /** @var array<Project> $projects */
        $projects = $serializer->denormalize($data, Project::class . '[]');

        $this->projects = new ProjectCollection(\Safe\array_combine(
            \array_map(
                static fn (Project $project): string => $slugger->slug($project->getName())->toString(),
                $projects
            ),
            $projects
        ));
    }

    public function getByName(string $name): Project
    {
        $key = $this->slugger->slug($name)->toString();

        $project = $this->projects->offsetGet($key);
        if (null === $project) {
            throw new InvalidArgumentException('Unable to find project with name ' . $name);
        }

        return $project;
    }

    public function getAll(): ProjectCollection
    {
        return $this->projects;
    }
}
