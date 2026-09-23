<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Tests\Unit\Http;

use NSolutions\Filmweb\Http\CachingTransport;
use NSolutions\Filmweb\Http\Response;
use NSolutions\Filmweb\Tests\Fixtures\ArrayCache;
use NSolutions\Filmweb\Tests\Fixtures\ScriptedTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CachingTransport::class)]
#[UsesClass(Response::class)]
final class CachingTransportTest extends TestCase
{
    public function testServesRepeatedRequestsFromCache(): void
    {
        $inner = new ScriptedTransport([new Response(200, '{"id":628}')]);
        $cache = new ArrayCache();
        $transport = new CachingTransport($inner, $cache, ttl: 60);

        $first = $transport->get('https://api.test/film/628', ['x-locale' => 'pl_PL']);
        $second = $transport->get('https://api.test/film/628', ['x-locale' => 'pl_PL']);

        self::assertSame('{"id":628}', $second->body);
        self::assertEquals($first, $second);
        self::assertSame(1, $inner->calls);
        self::assertSame([60], array_values($cache->ttls));
        self::assertStringStartsWith('filmweb.', (string) array_key_first($cache->values));
    }

    public function testCachesNotFoundButNotErrors(): void
    {
        $inner = new ScriptedTransport([new Response(404, ''), new Response(503, ''), new Response(503, '')]);
        $transport = new CachingTransport($inner, new ArrayCache());

        $transport->get('https://api.test/missing');
        $transport->get('https://api.test/missing');
        $transport->get('https://api.test/broken');
        $transport->get('https://api.test/broken');

        self::assertSame(3, $inner->calls);
    }

    public function testVariesCacheKeyByHeaders(): void
    {
        $inner = new ScriptedTransport([new Response(200, 'pl'), new Response(200, 'en')]);
        $transport = new CachingTransport($inner, new ArrayCache());

        self::assertSame('pl', $transport->get('https://api.test', ['x-locale' => 'pl_PL'])->body);
        self::assertSame('en', $transport->get('https://api.test', ['x-locale' => 'en_US'])->body);
    }
}
