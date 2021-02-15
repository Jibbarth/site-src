<?php

declare(strict_types=1);

namespace App\Collection;

use App\Model\Project;
use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<Project>
 */
final class ProjectCollection extends AbstractCollection implements SliceableCollection
{
    use SliceTrait;

    public function getType(): string
    {
        return Project::class;
    }
}
