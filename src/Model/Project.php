<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

final class Project
{
    private string $name;

    private string $description;
    /**
     * @Assert\Url()
     */
    private string $url;

    private string $role;

    private string $category;

    private ?string $image;

    /** @phpstan-ignore-next-line: ignore image with null value*/
    public function __construct(
        string $name,
        string $description,
        string $url,
        string $category,
        string $role,
        ?string $image = null
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->url = $url;
        $this->role = $role;
        $this->category = $category;
        $this->image = $image;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }
}
