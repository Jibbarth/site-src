<?php

declare(strict_types=1);

namespace App\Model;

final class Badge
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $image,
        public string $link,
    ) {
    }
}
