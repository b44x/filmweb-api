<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Http;

use Closure;
use NSolutions\Filmweb\Exception\TransportException;

/**
 * Decorator retrying failed requests (network errors, HTTP 429 and 5xx) with exponential backoff.
 *
 * ```php
 * $transport = new RetryingTransport(new CurlTransport(), maxRetries: 3);
 * ```
 */
final readonly class RetryingTransport implements Transport
{
    private const HTTP_TOO_MANY_REQUESTS = 429;
    private const HTTP_SERVER_ERROR = 500;

    /** @var Closure(int): void */
    private Closure $sleep;

    /**
     * @param int<0, max>                $maxRetries
     * @param int<0, max>                $baseDelayMs delay before the first retry, doubled for each next one
     * @param (Closure(int): void)|null $sleep       receives milliseconds; replace it in tests
     */
    public function __construct(
        private Transport $transport,
        private int $maxRetries = 2,
        private int $baseDelayMs = 500,
        ?Closure $sleep = null,
    ) {
        $this->sleep = $sleep ?? static function (int $milliseconds): void {
            usleep($milliseconds * 1000);
        };
    }

    public function get(string $url, array $headers = []): Response
    {
        for ($attempt = 0; ; ++$attempt) {
            try {
                $response = $this->transport->get($url, $headers);

                if (!self::isRetryable($response) || $attempt >= $this->maxRetries) {
                    return $response;
                }
            } catch (TransportException $e) {
                if ($attempt >= $this->maxRetries) {
                    throw $e;
                }
            }

            ($this->sleep)($this->baseDelayMs * 2 ** $attempt);
        }
    }

    private static function isRetryable(Response $response): bool
    {
        return $response->status === self::HTTP_TOO_MANY_REQUESTS || $response->status >= self::HTTP_SERVER_ERROR;
    }
}
