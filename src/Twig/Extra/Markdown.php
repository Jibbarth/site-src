<?php

declare(strict_types=1);

namespace App\Twig\Extra;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;
use Twig\Extra\Markdown\MarkdownInterface;

final class Markdown implements MarkdownInterface
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $environment = new Environment([
            'heading_permalink' => [
                'symbol' => '#',
            ],
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new HeadingPermalinkExtension());

        $this->converter = new MarkdownConverter($environment);
    }

    public function convert(string $body): string
    {
        return (string) $this->converter->convertToHtml($body);
    }
}
