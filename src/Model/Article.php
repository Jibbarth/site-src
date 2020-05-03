<?php

declare(strict_types=1);

namespace App\Model;

use App\Constant\ArticleType;
use Symfony\Component\Validator\Constraints as Assert;
use function Symfony\Component\String\u;

final class Article
{
    private string $id;

    private string $title;

    private ?string $content;

    private \DateTimeInterface $date;

    /**
     * @Assert\Url()
     */
    private ?string $url;

    private string $type;

    /**
     * @var array<string, array<string,string>>
     */
    private array $files = [];

    public function __construct(string $id, string $title, ?string $content, \DateTimeInterface $date, ?string $url)
    {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->url = $url;
        $this->date = $date;

        $this->type = ArticleType::INTERNAL;
        if (null === $content && null !== $url) {
            $this->type = ArticleType::EXTERNAL;
        }
        if (null !== u($url ?? '')->indexOf('gist.github.com')) {
            $this->type = ArticleType::GIST;
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAbstract(): string
    {
        return u($this->content ?? '')->truncate(200, '...')->toString();
    }

    /**
     * @return array<string, array<string,string>>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param array<string, array<string,string>> $files
     */
    public function withFiles(array $files): self
    {
        $this->files = $files;

        return $this;
    }
}
