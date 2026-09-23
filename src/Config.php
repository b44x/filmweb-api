<?php

declare(strict_types=1);

namespace NSolutions\Filmweb;

/**
 * Immutable configuration of the Filmweb REST API (`www.filmweb.pl/api/v1`).
 */
final readonly class Config
{
    public const DEFAULT_BASE_URL = 'https://www.filmweb.pl/api/v1';
    public const DEFAULT_CDN_URL = 'https://fwcdn.pl';
    public const DEFAULT_LOCALE = 'pl_PL';
    public const DEFAULT_USER_AGENT = 'Mozilla/5.0 (compatible; FilmwebApiClient/2.0; +https://github.com/b44x/filmweb-api)';

    public function __construct(
        public string $baseUrl = self::DEFAULT_BASE_URL,
        public string $cdnUrl = self::DEFAULT_CDN_URL,
        public string $locale = self::DEFAULT_LOCALE,
        public string $userAgent = self::DEFAULT_USER_AGENT,
    ) {}
}
