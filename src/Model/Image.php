<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Model;

use JsonSerializable;

/**
 * Image on the Filmweb CDN. The API returns paths with a `$` size placeholder,
 * e.g. `/06/28/628/7685907_1.$.jpg`; {@see url()} fills it in.
 */
final readonly class Image implements JsonSerializable
{
    public const CDN_URL = 'https://fwcdn.pl';
    public const DEFAULT_SIZE = 3;

    public function __construct(
        public ImageKind $kind,
        public string $path,
    ) {}

    public static function tryFrom(ImageKind $kind, ?string $path): ?self
    {
        return $path === null || $path === '' ? null : new self($kind, $path);
    }

    /**
     * @param positive-int $size Filmweb size variant (larger number = larger image)
     */
    public function url(int $size = self::DEFAULT_SIZE): string
    {
        return \sprintf('%s/%s/%s', self::CDN_URL, $this->kind->value, ltrim(str_replace('$', (string) $size, $this->path), '/'));
    }

    public function jsonSerialize(): string
    {
        return $this->url();
    }

    public function __toString(): string
    {
        return $this->url();
    }
}
