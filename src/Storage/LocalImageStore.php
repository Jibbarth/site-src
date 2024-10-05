<?php

declare(strict_types=1);

namespace App\Storage;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

use function Symfony\Component\String\u;

/**
 * Copy an image content in public storage and return it path
 */
final class LocalImageStore
{
    public function __construct(
        private Filesystem $filesystem,
        #[Autowire('%kernel.project_dir%/public')]
        private string $publicDir,
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function store(string $content, string $name, string $folder = 'images'): string
    {
        $folderPath = Path::join($this->publicDir, $folder);
        if (!$this->filesystem->exists($folderPath)) {
            $this->filesystem->mkdir($folderPath);
        }

        $this->filesystem->dumpFile(Path::join($folderPath, $name), $content);

        return u(Path::join($folder, $name))->ensureStart('/')->toString();
    }
}
