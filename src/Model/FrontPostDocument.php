<?php

declare(strict_types=1);

namespace App\Model;

final class FrontPostDocument
{
    public function __construct(
        public string $title,
        public string $content,
        public \DateTimeInterface $date,
        public string $state,
        public ?string $image,
    ) {
    }
}
