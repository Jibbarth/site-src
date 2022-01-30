<?php

declare(strict_types=1);

namespace App\Parser;

use App\Constant\ArticleStatus;
use App\Model\FrontPostDocument;
use Mni\FrontYAML\Parser;

final class FrontYamlParser
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser(new YamlParser());
    }

    public function parse(string $content): FrontPostDocument
    {
        $document = $this->parser->parse($content, false);
        /**
         * @var array{
         *     title: string,
         *     date: \DateTimeInterface,
         *     state: ?string,
         *     hero: ?string,
         * } $yaml
         */
        $yaml = $document->getYAML();

        return new FrontPostDocument(
            $yaml['title'],
            $document->getContent(),
            $yaml['date'],
            $yaml['state'] ?? ArticleStatus::PUBLISHED,
            $yaml['hero'] ?? null,
        );
    }
}
