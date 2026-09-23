<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Http;

use CurlHandle;
use NSolutions\Filmweb\Exception\TransportException;

/**
 * Dependency-free transport based on ext-curl. The handle is reused between
 * requests, so connections (and TLS sessions) are kept alive.
 */
final class CurlTransport implements Transport
{
    private ?CurlHandle $handle = null;

    public function __construct(
        private readonly int $timeout = 10,
        private readonly int $connectTimeout = 5,
    ) {}

    public function get(string $url, array $headers = []): Response
    {
        if ($url === '') {
            throw new TransportException('URL must not be empty.');
        }

        $handle = $this->handle();

        curl_setopt_array($handle, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => array_map(
                static fn(string $name, string $value): string => "{$name}: {$value}",
                array_keys($headers),
                $headers,
            ),
        ]);

        $body = curl_exec($handle);

        if (!\is_string($body)) {
            throw new TransportException(\sprintf('cURL error #%d: %s', curl_errno($handle), curl_error($handle)));
        }

        return new Response(curl_getinfo($handle, CURLINFO_RESPONSE_CODE), $body);
    }

    private function handle(): CurlHandle
    {
        if ($this->handle !== null) {
            return $this->handle;
        }

        if (!\extension_loaded('curl')) {
            throw new TransportException('ext-curl is required by CurlTransport. Install it or use Psr18Transport.');
        }

        $handle = curl_init();

        if ($handle === false) {
            throw new TransportException('Unable to initialise cURL.');
        }

        curl_setopt_array($handle, [
            CURLOPT_HTTPGET => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_ENCODING => '',
        ]);

        return $this->handle = $handle;
    }
}
