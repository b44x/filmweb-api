<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Tests\Fixtures;

use NSolutions\Filmweb\Http\Response;
use NSolutions\Filmweb\Http\Transport;

/**
 * In-memory transport: maps URL paths to canned responses and records requests.
 */
final class FakeTransport implements Transport
{
    /** @var list<string> */
    public array $requestedUrls = [];

    /** @var array<string, string> */
    public array $lastHeaders = [];

    /**
     * @param array<string, Response> $routes path (with query string) => response
     */
    public function __construct(private array $routes = []) {}

    /**
     * Serves `tests/Fixtures/responses/<file>.json` for the given path.
     */
    public function withFixture(string $path, string $file): self
    {
        $body = file_get_contents(__DIR__ . "/responses/{$file}.json");
        \assert(\is_string($body));

        return $this->with($path, new Response(200, $body));
    }

    public function with(string $path, Response $response): self
    {
        $this->routes[$path] = $response;

        return $this;
    }

    public function get(string $url, array $headers = []): Response
    {
        $this->requestedUrls[] = $url;
        $this->lastHeaders = $headers;

        $path = (string) preg_replace('~^https?://[^/]+/api/v1~', '', $url);

        return $this->routes[$path] ?? new Response(404, '{"error":"not found"}');
    }
}
