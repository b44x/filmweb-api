<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Support;

/**
 * Builds absolute CDN URLs from image paths returned by the API.
 *
 * Paths contain a `$` placeholder for the size variant, e.g.
 * `/06/28/628/7685907_1.$.jpg` → `https://fwcdn.pl/fpo/06/28/628/7685907_1.6.jpg`.
 */
final readonly class ImageUrls
{
    public const DEFAULT_SIZE = 6;

    public function __construct(private string $cdnUrl) {}

    public function poster(?string $path, int $size = self::DEFAULT_SIZE): ?string
    {
        return $this->resolve('fpo', $path, $size);
    }

    public function person(?string $path, int $size = self::DEFAULT_SIZE): ?string
    {
        return $this->resolve('ppo', $path, $size);
    }

    private function resolve(string $directory, ?string $path, int $size): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return \sprintf('%s/%s/%s', rtrim($this->cdnUrl, '/'), $directory, ltrim(str_replace('$', (string) $size, $path), '/'));
    }
}
