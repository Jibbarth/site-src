<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@hotwired/turbo' => [
        'version' => '8.0.4',
    ],
    'prismjs' => [
        'version' => '1.29.0',
    ],
    'prismjs/themes/prism.min.css' => [
        'version' => '1.29.0',
        'type' => 'css',
    ],
    'prism-themes' => [
        'version' => '1.9.0',
    ],
    'jsonp' => [
        'version' => '0.2.1',
    ],
    'debug' => [
        'version' => '4.3.4',
    ],
    'ms' => [
        'version' => '2.1.3',
    ],
    'prism-themes/themes/prism-vsc-dark-plus.css' => [
        'version' => '1.9.0',
        'type' => 'css',
    ],
    'prismjs/components/prism-core' => [
        'version' => '1.29.0',
    ],
    'prismjs/plugins/autoloader/prism-autoloader' => [
        'version' => '1.29.0',
    ],
    'prismjs/components/prism-ini.min.js' => [
        'version' => '1.29.0',
    ],
    'prismjs/components/prism-bash.min.js' => [
        'version' => '1.29.0',
    ],
    'prismjs/components/prism-css.min.js' => [
        'version' => '1.29.0',
    ],
    'prismjs/components/prism-diff.min.js' => [
        'version' => '1.29.0',
    ],
    'prismjs/components/prism-javascript.min.js' => [
        'version' => '1.29.0',
    ],
    'prismjs/components/prism-json.min.js' => [
        'version' => '1.29.0',
    ],
    'prismjs/components/prism-markup.min.js' => [
        'version' => '1.29.0',
    ],
    'prismjs/components/prism-php.min.js' => [
        'version' => '1.29.0',
    ],
    'prismjs/components/prism-twig.min.js' => [
        'version' => '1.29.0',
    ],
    'prismjs/components/prism-yaml.min.js' => [
        'version' => '1.29.0',
    ],
    'prismjs/components/prism-clike.min.js' => [
        'version' => '1.29.0',
    ],
    'prismjs/components/prism-markup-templating.min.js' => [
        'version' => '1.29.0',
    ],
    'prism-themes/themes/prism-vsc-dark-plus.min.css' => [
        'version' => '1.9.0',
        'type' => 'css',
    ],
    'prismjs/components/prism-core.min.js' => [
        'version' => '1.29.0',
    ],
];
