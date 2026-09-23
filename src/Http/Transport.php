<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Http;

use NSolutions\Filmweb\Exception\TransportException;

/**
 * Performs a single HTTP GET request. HTTP error statuses are returned, not thrown.
 */
interface Transport
{
    /**
     * @param array<string, string> $headers
     *
     * @throws TransportException when no response could be obtained
     */
    public function get(string $url, array $headers = []): Response;
}
