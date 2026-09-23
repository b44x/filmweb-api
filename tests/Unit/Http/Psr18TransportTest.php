<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Tests\Unit\Http;

use NSolutions\Filmweb\Exception\TransportException;
use NSolutions\Filmweb\Http\Psr18Transport;
use NSolutions\Filmweb\Http\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

#[CoversClass(Psr18Transport::class)]
#[UsesClass(Response::class)]
final class Psr18TransportTest extends TestCase
{
    public function testSendsGetRequestWithHeaders(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->expects(self::exactly(2))->method('withHeader')->willReturnSelf();

        $factory = $this->createMock(RequestFactoryInterface::class);
        $factory->expects(self::once())->method('createRequest')->with('GET', 'https://api.test/x')->willReturn($request);

        $body = self::createStub(StreamInterface::class);
        $body->method('__toString')->willReturn('{"ok":true}');

        $psrResponse = self::createStub(ResponseInterface::class);
        $psrResponse->method('getStatusCode')->willReturn(404);
        $psrResponse->method('getBody')->willReturn($body);

        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())->method('sendRequest')->with($request)->willReturn($psrResponse);

        $response = (new Psr18Transport($client, $factory))->get('https://api.test/x', ['Accept' => 'application/json', 'x-locale' => 'pl_PL']);

        self::assertSame(404, $response->status);
        self::assertSame('{"ok":true}', $response->body);
        self::assertFalse($response->isSuccessful());
    }

    public function testWrapsClientExceptions(): void
    {
        $factory = self::createStub(RequestFactoryInterface::class);
        $factory->method('createRequest')->willReturn(self::createStub(RequestInterface::class));

        $client = self::createStub(ClientInterface::class);
        $client->method('sendRequest')->willThrowException(new class ('timeout') extends RuntimeException implements ClientExceptionInterface {});

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('timeout');

        (new Psr18Transport($client, $factory))->get('https://api.test/x');
    }
}
