<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class FrontExtension extends AbstractExtension
{
    private Environment $renderer;

    private ParameterBagInterface $parameterBag;

    public function __construct(Environment $renderer, ParameterBagInterface $parameterBag)
    {
        $this->renderer = $renderer;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @return array<\Twig\TwigFilter>
     */
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            // new TwigFilter('filter_name', [$this, 'doSomething']),
        ];
    }

    /**
     * @return array<\Twig\TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('interests', [$this, 'interests'], ['is_safe' => ['html']]),
            new TwigFunction('social_link', [$this, 'socialLink'], ['is_safe' => ['html']]),
        ];
    }

    public function interests(): string
    {
        return $this->renderer->render('_partials/interests.html.twig', [
            'interests' => $this->parameterBag->get('interests'),
        ]);
    }

    public function socialLink(): string
    {
        return $this->renderer->render('_partials/social.html.twig', [
            'data' => $this->parameterBag->get('social'),
        ]);
    }
}
