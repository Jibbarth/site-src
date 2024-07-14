<?php

declare(strict_types=1);

namespace App\Photo;

use App\Model\Photo;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Unsplash\HttpClient;
use Unsplash\Photo as UnsplashPhoto;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
final class UnsplashRandomPhotoProvider
{
    private const QUERY_PARAM_PATTERN = '&auto=format&w={width}&h={height}&fit=crop&crop=edges,entropy';
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

        $photo = UnsplashPhoto::random([
            'query' => $keyword,
            'w' => $width,
            'h' => $height,
        ]);

        $appPhoto = new Photo();

        $blurhash = $photo->__get('blur_hash');
        if (!\is_string($blurhash)) {
            throw new \LogicException('Unable to get blurhash');
        }

        $appPhoto->blurhash = $this->blurhashConverter->convert($blurhash, $width, $height);

        $urls = $photo->__get('urls');
        if (!\is_array($urls) || !\array_key_exists('full', $urls)) {
            throw new \LogicException('Unable to get full url');
        }
        $appPhoto->fullUrl = $urls['full'] . strtr(self::QUERY_PARAM_PATTERN, ['{width}' => $width, '{height}' => $height]);

        return $appPhoto;
    }
}
