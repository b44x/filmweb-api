<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Tests\Unit\Api;

use NSolutions\Filmweb\Api\ApiClient;
use NSolutions\Filmweb\Config;
use NSolutions\Filmweb\Exception\ApiException;
use NSolutions\Filmweb\Exception\UnexpectedResponseException;
use NSolutions\Filmweb\Http\Response;
use NSolutions\Filmweb\Support\Data;
use NSolutions\Filmweb\Tests\Fixtures\FakeTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ApiClient::class)]
#[UsesClass(Config::class)]
#[UsesClass(Data::class)]
#[UsesClass(Response::class)]
final class ApiClientTest extends TestCase
{
    public function testBuildsUrlWithEncodedQuery(): void
    {
        $transport = (new FakeTransport())->with('/live/search?query=za%C5%BC%C3%B3%C5%82%C4%87%20g%C4%99%C5%9Bl%C4%85', new Response(200, '{}'));

        $data = (new ApiClient($transport, new Config(baseUrl: 'https://www.filmweb.pl/api/v1/')))
            ->fetch('live/search', ['query' => 'zażółć gęślą']);

        self::assertSame([], $data?->toArray());
    }

    public function testThrowsOnHttpError(): void
    {
        $transport = (new FakeTransport())->with('/film/1/rating', new Response(429, 'Too Many Requests'));

        try {
            (new ApiClient($transport))->fetch('/film/1/rating');
            self::fail('Expected ApiException.');
        } catch (ApiException $e) {
            self::assertSame(429, $e->status());
        }
    }

    public function testThrowsOnInvalidJson(): void
    {
        $transport = (new FakeTransport())->with('/film/1/rating', new Response(200, '<html>'));

        $this->expectException(UnexpectedResponseException::class);

        (new ApiClient($transport))->fetch('/film/1/rating');
    }
}
