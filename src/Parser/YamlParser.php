<?php

declare(strict_types=1);

namespace App\Parser;

use Mni\FrontYAML\YAML\YAMLParser as YamlParserInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

final class YamlParser implements YamlParserInterface
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $yaml)
    {
        return $this->parser->parse($yaml, Yaml::PARSE_DATETIME);
    }
}
