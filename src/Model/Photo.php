<?php

declare(strict_types=1);

namespace App\Model;

final class Photo
{
    public string $fullUrl;

    /**
     * A blurhash that has been previously transformed in base64
     */
    public string $blurhash = '';
}
