<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Tests\Unit\Http;

use NSolutions\Filmweb\Exception\TransportException;
use NSolutions\Filmweb\Http\Response;
use NSolutions\Filmweb\Http\RetryingTransport;
use NSolutions\Filmweb\Tests\Fixtures\ScriptedTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RetryingTransport::class)]
#[UsesClass(Response::class)]
final class RetryingTransportTest extends TestCase
{
    /** @var list<int> */
    private array $sleeps = [];

    public function testRetriesRateLimitsAndServerErrorsWithExponentialBackoff(): void
    {
        $inner = new ScriptedTransport([new Response(429, ''), new Response(503, ''), new Response(200, 'ok')]);

        $response = $this->transport($inner, maxRetries: 3)->get('https://api.test');

        self::assertSame('ok', $response->body);
        self::assertSame(3, $inner->calls);
        self::assertSame([100, 200], $this->sleeps);
    }

    public function testRetriesNetworkErrors(): void
    {
        $inner = new ScriptedTransport([new TransportException('timeout'), new Response(200, 'ok')]);

        self::assertSame('ok', $this->transport($inner)->get('https://api.test')->body);
    }

    public function testDoesNotRetryClientErrors(): void
    {
        $inner = new ScriptedTransport([new Response(404, '')]);

        self::assertSame(404, $this->transport($inner)->get('https://api.test')->status);
        self::assertSame([], $this->sleeps);
    }

    public function testReturnsLastResponseWhenRetriesAreExhausted(): void
    {
        $inner = new ScriptedTransport([new Response(500, ''), new Response(500, ''), new Response(502, '')]);

        self::assertSame(502, $this->transport($inner, maxRetries: 2)->get('https://api.test')->status);
        self::assertSame(3, $inner->calls);
    }

    public function testRethrowsNetworkErrorWhenRetriesAreExhausted(): void
    {
        $inner = new ScriptedTransport([new TransportException('down'), new TransportException('still down')]);

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('still down');

        $this->transport($inner, maxRetries: 1)->get('https://api.test');
    }

    /**
     * @param int<0, max> $maxRetries
     */
    private function transport(ScriptedTransport $inner, int $maxRetries = 2): RetryingTransport
    {
        return new RetryingTransport($inner, $maxRetries, baseDelayMs: 100, sleep: function (int $milliseconds): void {
            $this->sleeps[] = $milliseconds;
        });
    }
}
