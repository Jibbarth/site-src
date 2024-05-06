<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    Symfony\UX\StimulusBundle\StimulusBundle::class => ['all' => true],
    //Symplify\SymfonyStaticDumper\SymfonyStaticDumperBundle::class => ['all' => true],
    Symfony\UX\Turbo\TurboBundle::class => ['all' => true],
    Symfony\UX\LazyImage\LazyImageBundle::class => ['all' => true],
    Symfony\UX\Icons\UXIconsBundle::class => ['all' => true],
    Symfonycasts\TailwindBundle\SymfonycastsTailwindBundle::class => ['all' => true],
];
