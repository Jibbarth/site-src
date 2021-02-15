<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use function Symfony\Component\String\u;

final class UrlSanitizerExtension extends AbstractExtension
{
    /**
     * @return array<\Twig\TwigFilter>
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('sanitize_url', [$this, 'sanitizeUrl']),
        ];
    }

    public function sanitizeUrl(string $url): string
    {
        $host = \Safe\parse_url($url, \PHP_URL_HOST);
        if (null === $host) {
            throw new \InvalidArgumentException(\sprintf('"%s" is not an absolute url', $url));
        }

        $sanitizedHost = \idn_to_ascii($host, \IDNA_NONTRANSITIONAL_TO_ASCII, \INTL_IDNA_VARIANT_UTS46);
        if (false === $sanitizedHost) {
            $sanitizedHost = \mb_strtolower($host);
        }

        return u($url)->replace($host, $sanitizedHost)->toString();
    }
}
