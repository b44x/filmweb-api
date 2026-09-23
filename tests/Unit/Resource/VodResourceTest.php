<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Tests\Unit\Resource;

use DateTimeImmutable;
use NSolutions\Filmweb\Api\ApiClient;
use NSolutions\Filmweb\Api\Endpoint\Vod\GetFilmVodOffers;
use NSolutions\Filmweb\Api\Endpoint\Vod\GetVodProviders;
use NSolutions\Filmweb\Config;
use NSolutions\Filmweb\Model\VodOffer;
use NSolutions\Filmweb\Resource\VodResource;
use NSolutions\Filmweb\Support\Data;
use NSolutions\Filmweb\Tests\Fixtures\FakeTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VodResource::class)]
#[CoversClass(GetVodProviders::class)]
#[CoversClass(GetFilmVodOffers::class)]
#[CoversClass(VodOffer::class)]
#[UsesClass(ApiClient::class)]
#[UsesClass(Config::class)]
#[UsesClass(Data::class)]
final class VodResourceTest extends TestCase
{
    private FakeTransport $transport;
    private VodResource $vod;

    protected function setUp(): void
    {
        $this->transport = (new FakeTransport())
            ->withFixture('/vod/providers/list', 'vod-providers')
            ->withFixture('/vod/film/500891/providers/list', 'film-vod-offers');
        $this->vod = new VodResource(new ApiClient($this->transport));
    }

    public function testListsOffersAvailableAtTheGivenMoment(): void
    {
        $offers = $this->vod->offers(500891, new DateTimeImmutable('2026-09-23T12:00:00Z'));

        self::assertCount(3, $offers, 'the offer starting in 2030 is filtered out');

        [$chili, $tvSmart, $megogo] = $offers;

        self::assertSame('CHILI', $chili->provider?->name);
        self::assertSame(39.9, $chili->buyPrice);
        self::assertSame(9.9, $chili->rentPrice);
        self::assertFalse($chili->subscription);

        self::assertSame('TVSmart', $tvSmart->provider?->name);
        self::assertNull($tvSmart->buyPrice);
        self::assertSame(8.9, $tvSmart->rentPrice);

        self::assertTrue($megogo->subscription);
        self::assertNull($megogo->rentPrice);
        self::assertSame('2026-09-30T21:00:00+00:00', $megogo->availableUntil?->format(DATE_ATOM));
    }

    public function testDropsExpiredOffers(): void
    {
        self::assertCount(2, $this->vod->offers(500891, new DateTimeImmutable('2026-10-01T00:00:00Z')));
    }

    public function testFetchesProviderDictionaryOnlyOnce(): void
    {
        $this->vod->offers(500891);
        $this->vod->offers(500891);

        self::assertSame('Netflix', $this->vod->providers()[2]->name);
        self::assertCount(1, array_filter($this->transport->requestedUrls, static fn(string $url): bool => str_ends_with($url, '/vod/providers/list')));
    }

    public function testReturnsEmptyListForUnknownFilm(): void
    {
        self::assertSame([], $this->vod->offers(1));
    }
}
