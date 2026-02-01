<?php

declare(strict_types=1);

namespace App\Photo;

use App\Model\Photo;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Unsplash\HttpClient;
use Unsplash\Photo as UnsplashPhoto;

/**
 * @SuppressWarnings(StaticAccess)
 */
final class UnsplashRandomPhotoProvider
{
    private const QUERY_PARAM_PATTERN = 'auto=format&w={width}&h={height}&fit=crop&crop=edges,entropy';
    private const DEFAULT_PHOTO_URLS = [
        'https://images.unsplash.com/photo-1763568258286-721c164171e0?',
        'https://images.unsplash.com/photo-1605379399642-870262d3d051?',
        'https://images.unsplash.com/photo-1619410283995-43d9134e7656?',
        'https://images.unsplash.com/photo-1498050108023-c5249f4df085?',
        'https://images.unsplash.com/photo-1571171637578-41bc2dd41cd2?',
        'https://images.unsplash.com/photo-1499951360447-b19be8fe80f5?',
        'https://images.unsplash.com/photo-1585076641399-5c06d1b3365f?',
    ];
    private bool $initialized = false;

    public function __construct(
        private BlurHashToBase64Converter $blurhashConverter,
        #[Autowire(env: 'UNSPLASH_ACCESS_KEY')]
        private string $accessKey,
        #[Autowire(env: 'UNSPLASH_APP_NAME')]
        private string $appName,
    ) {}

    public function find(string $keyword, int $width, int $height): Photo
    {
        if (!$this->initialized) {
            HttpClient::init([
                'applicationId' => $this->accessKey,
                'utmSource' => $this->appName,
            ]);

            $this->initialized = true;
        }

        try {
            $photo = UnsplashPhoto::random([
                'query' => $keyword,
                'w' => $width,
                'h' => $height,
            ]);
        } catch (\Throwable $e) {
            $appPhoto = new Photo();
            $appPhoto->fullUrl = $this->pickFallbackImage() . strtr(self::QUERY_PARAM_PATTERN, ['{width}' => $width, '{height}' => $height]);

            return $appPhoto;
        }

        $appPhoto = new Photo();

        $blurhash = $photo->__get('blur_hash');
        if (!\is_string($blurhash)) {
            throw new \LogicException('Unable to get blurhash');
        }

        $appPhoto->blurhash = $this->blurhashConverter->convert($blurhash, $width, $height);

        $urls = $photo->__get('urls');
        if (!\is_array($urls) || !\array_key_exists('full', $urls) || !\is_string($urls['full'])) {
            throw new \LogicException('Unable to get full url');
        }
        $appPhoto->fullUrl = $urls['full'] . '&' . strtr(self::QUERY_PARAM_PATTERN, ['{width}' => $width, '{height}' => $height]);

        return $appPhoto;
    }

    private function pickFallbackImage(): string
    {
        return self::DEFAULT_PHOTO_URLS[array_rand(self::DEFAULT_PHOTO_URLS)];
    }
}
