<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    // Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true], // Comentado temporalmente
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    // Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true], // Comentado temporalmente
    // Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true], // Comentado temporalmente
    // Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true], // Comentado temporalmente
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    Symfony\WebpackEncoreBundle\WebpackEncoreBundle::class => ['all' => true],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
    Symfony\UX\StimulusBundle\StimulusBundle::class => ['all' => true],
    Symfony\UX\Turbo\TurboBundle::class => ['all' => true],
    // Nelmio\CorsBundle\NelmioCorsBundle::class => ['all' => true], // Comentado hasta que se instale el paquete
];
