<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class FrontExtension extends AbstractExtension
{
    public function __construct(private Environment $renderer, private ParameterBagInterface $parameterBag)
    {
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
