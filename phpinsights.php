<?php

declare(strict_types=1);

return [
    'preset' => 'symfony',
    'ide' => null,
    'exclude' => [
        'src/Kernel.php',
    ],
    'add' => [
        \NunoMaduro\PhpInsights\Domain\Metrics\Code\Code::class => [
            \SlevomatCodingStandard\Sniffs\ControlStructures\RequireYodaComparisonSniff::class,
        ],
    ],
    'remove' => [
        \SlevomatCodingStandard\Sniffs\Classes\SuperfluousInterfaceNamingSniff::class,
        \PHP_CodeSniffer\Standards\Generic\Sniffs\Formatting\SpaceAfterNotSniff::class,
        \SlevomatCodingStandard\Sniffs\ControlStructures\DisallowYodaComparisonSniff::class,
        \NunoMaduro\PhpInsights\Domain\Insights\ForbiddenTraits::class,
        \NunoMaduro\PhpInsights\Domain\Insights\ForbiddenSecurityIssues::class,
        \SlevomatCodingStandard\Sniffs\Classes\SuperfluousTraitNamingSniff::class,
        // Implicit constructor is not well handle for now
        \SlevomatCodingStandard\Sniffs\Functions\UnusedParameterSniff::class,
    ],
    'config' => [
        \PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff::class => [
            'lineLimit' => 120,
            'absoluteLineLimit' => 160,
        ],
        \PhpCsFixer\Fixer\Import\OrderedImportsFixer::class => [
            'imports_order' => ['const', 'class', 'function'],
        ],
    ],
    'requirements' => [
        'min-quality' => 100,
        // 'min-complexity' => 0,
        'min-architecture' => 90,
        'min-style' => 98,
    ],
];
