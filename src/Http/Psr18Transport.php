<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Http;

use NSolutions\Filmweb\Exception\TransportException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * Adapter for any PSR-18 HTTP client (Guzzle, Symfony HttpClient, Buzz…).
 */
final readonly class Psr18Transport implements Transport
{
    public function __construct(
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
    ) {}

    public function get(string $url, array $headers = []): Response
    {
        $request = $this->requestFactory->createRequest('GET', $url);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new TransportException($e->getMessage(), previous: $e);
        }

        return new Response($response->getStatusCode(), (string) $response->getBody());
    }
}
