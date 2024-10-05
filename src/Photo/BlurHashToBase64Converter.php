<?php

declare(strict_types=1);

namespace App\Photo;

use Intervention\Image\Colors\Rgb\Color;
use Intervention\Image\Drivers\Gd\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;
use kornrunner\Blurhash\Blurhash as BlurhashEncoder;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class BlurHashToBase64Converter
{
    public function __construct(
        #[Autowire(service: 'lazy_image.image_manager')]
        private ImageManager $imageManager,
    ) {}

    public function convert(string $blurhash, int $width, int $height): string
    {
        $pixels = (new BlurhashEncoder())::decode($blurhash, $width, $height);

        $thumbnail = $this->imageManager->create($width, $height);

        for ($y = 0; $y < $height; ++$y) {
            for ($x = 0; $x < $width; ++$x) {
                $thumbnail->drawPixel($x, $y, new Color($pixels[$y][$x][0], $pixels[$y][$x][1], $pixels[$y][$x][2]));
            }
        }

        return $thumbnail->encode(new JpegEncoder(80))->toDataUri();
    }
}
