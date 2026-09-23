<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Http;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Decorator caching successful and 404 responses in any PSR-16 cache.
 *
 * ```php
 * $transport = new CachingTransport(new CurlTransport(), $cache, ttl: 3600);
 * ```
 */
final readonly class CachingTransport implements Transport
{
    private const HTTP_NOT_FOUND = 404;

    /**
     * @param int<1, max> $ttl seconds
     */
    public function __construct(
        private Transport $transport,
        private CacheInterface $cache,
        private int $ttl = 3600,
        private string $prefix = 'filmweb.',
    ) {}

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $url, array $headers = []): Response
    {
        ksort($headers);
        $key = $this->prefix . hash('xxh128', $url . "\n" . json_encode($headers, JSON_THROW_ON_ERROR));

        $cached = $this->cache->get($key);

        if ($cached instanceof Response) {
            return $cached;
        }

        $response = $this->transport->get($url, $headers);

        if ($response->isSuccessful() || $response->status === self::HTTP_NOT_FOUND) {
            $this->cache->set($key, $response, $this->ttl);
        }

        return $response;
    }
}
