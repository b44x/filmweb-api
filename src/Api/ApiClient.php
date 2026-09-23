<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api;

use JsonException;
use NSolutions\Filmweb\Config;
use NSolutions\Filmweb\Exception\ApiException;
use NSolutions\Filmweb\Exception\FilmwebException;
use NSolutions\Filmweb\Exception\UnexpectedResponseException;
use NSolutions\Filmweb\Http\Transport;
use NSolutions\Filmweb\Support\Data;

/**
 * Low-level client: builds the URL, sends the request, decodes JSON and maps it.
 */
final readonly class ApiClient
{
    private const HTTP_NOT_FOUND = 404;

    public function __construct(
        private Transport $transport,
        private Config $config = new Config(),
    ) {}

    /**
     * @template TResult
     *
     * @param Endpoint<TResult> $endpoint
     *
     * @return TResult|null `null` when Filmweb responds with 404
     *
     * @throws FilmwebException
     */
    public function call(Endpoint $endpoint): mixed
    {
        $data = $this->fetch($endpoint->path(), $endpoint->query());

        return $data === null ? null : $endpoint->map($data);
    }

    /**
     * Fetches any API path and returns the decoded JSON (`null` on 404).
     *
     * @param array<string, string> $query
     *
     * @throws FilmwebException
     */
    public function fetch(string $path, array $query = []): ?Data
    {
        $url = $this->url($path, $query);
        $response = $this->transport->get($url, [
            'Accept' => 'application/json',
            'User-Agent' => $this->config->userAgent,
            'x-locale' => $this->config->locale,
        ]);

        if ($response->status === self::HTTP_NOT_FOUND) {
            return null;
        }

        if (!$response->isSuccessful()) {
            throw ApiException::fromStatus($response->status, $url);
        }

        try {
            return Data::of(json_decode($response->body, true, flags: JSON_THROW_ON_ERROR));
        } catch (JsonException $e) {
            throw new UnexpectedResponseException(\sprintf('Invalid JSON received from %s: %s', $url, $e->getMessage()), previous: $e);
        }
    }

    /**
     * @param array<string, string> $query
     */
    private function url(string $path, array $query): string
    {
        $url = rtrim($this->config->baseUrl, '/') . '/' . ltrim($path, '/');

        return $query === [] ? $url : $url . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }
}
