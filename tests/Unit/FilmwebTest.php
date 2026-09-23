<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Tests\Unit;

use NSolutions\Filmweb\Api\ApiClient;
use NSolutions\Filmweb\Api\Endpoint\Search\SearchTitles;
use NSolutions\Filmweb\Api\Mapping\PersonRefMapper;
use NSolutions\Filmweb\Config;
use NSolutions\Filmweb\Exception\InvalidArgumentException;
use NSolutions\Filmweb\Filmweb;
use NSolutions\Filmweb\Http\Response;
use NSolutions\Filmweb\Model\PersonRef;
use NSolutions\Filmweb\Model\SearchHit;
use NSolutions\Filmweb\Model\TitleType;
use NSolutions\Filmweb\Resource\FilmResource;
use NSolutions\Filmweb\Resource\PersonResource;
use NSolutions\Filmweb\Resource\VodResource;
use NSolutions\Filmweb\Support\Data;
use NSolutions\Filmweb\Tests\Fixtures\FakeTransport;
use NSolutions\Filmweb\Tests\Fixtures\GetUserId;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Filmweb::class)]
#[CoversClass(SearchTitles::class)]
#[CoversClass(SearchHit::class)]
#[CoversClass(PersonRefMapper::class)]
#[UsesClass(ApiClient::class)]
#[UsesClass(Config::class)]
#[UsesClass(Data::class)]
#[UsesClass(Response::class)]
#[UsesClass(FilmResource::class)]
#[UsesClass(PersonResource::class)]
#[UsesClass(VodResource::class)]
final class FilmwebTest extends TestCase
{
    public function testSearchesTitles(): void
    {
        $transport = (new FakeTransport())->withFixture('/live/search?query=matrix%20reaktywacja', 'live-search');

        $hits = Filmweb::create($transport)->search('  matrix reaktywacja ');

        self::assertCount(3, $hits);
        self::assertSame(628, $hits[0]->id);
        self::assertSame('Matrix', $hits[0]->title);
        self::assertSame(TitleType::Film, $hits[0]->titleType());
        self::assertEquals([new PersonRef(87, 'Keanu Reeves'), new PersonRef(315, 'Carrie-Anne Moss')], $hits[0]->mainCast);
        self::assertSame(TitleType::Serial, $hits[2]->titleType());
        self::assertSame([], $hits[2]->mainCast);
    }

    public function testRejectsEmptySearchQuery(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must not be empty');

        Filmweb::create(new FakeTransport())->search('   ');
    }

    public function testSendsLocaleAndJsonHeaders(): void
    {
        $transport = new FakeTransport();

        Filmweb::create($transport, new Config(locale: 'en_US', userAgent: 'Test/1.0'))->films->info(1);

        self::assertSame(['https://www.filmweb.pl/api/v1/title/1/info'], $transport->requestedUrls);
        self::assertSame(['Accept' => 'application/json', 'User-Agent' => 'Test/1.0', 'x-locale' => 'en_US'], $transport->lastHeaders);
    }

    public function testRunsCustomEndpointsAndRawPaths(): void
    {
        $transport = (new FakeTransport())->with('/users/Shadow_filmweb/id', new Response(200, '{"name":"Shadow_filmweb","userId":1681862}'));
        $filmweb = Filmweb::create($transport);

        self::assertSame(1681862, $filmweb->call(new GetUserId('Shadow_filmweb')));
        self::assertSame(['name' => 'Shadow_filmweb', 'userId' => 1681862], $filmweb->raw('/users/Shadow_filmweb/id')?->toArray());
        self::assertNull($filmweb->raw('/nope'));
    }
}
