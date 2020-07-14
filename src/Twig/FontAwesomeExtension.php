<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class FontAwesomeExtension extends AbstractExtension
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @return array<\Twig\TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('fa', [$this, 'getFontAwesomeSvgLink'], ['is_safe' => ['html']]),
        ];
    }

    public function getFontAwesomeSvgLink(string $id, string $collection = 'regular', int $size = 15): string
    {
        return $this->twig->render('_partials/font_awesome_svg_img.html.twig', [
            'id' => $id,
            'collection' => $collection,
            'size' => $size,
        ]);
    }
}
