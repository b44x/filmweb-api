<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Tests\Unit\Resource;

use NSolutions\Filmweb\Api\ApiClient;
use NSolutions\Filmweb\Api\Endpoint\Film\FilmEndpoint;
use NSolutions\Filmweb\Api\Endpoint\Film\GetCriticsRating;
use NSolutions\Filmweb\Api\Endpoint\Film\GetFilmDates;
use NSolutions\Filmweb\Api\Endpoint\Film\GetFilmDescription;
use NSolutions\Filmweb\Api\Endpoint\Film\GetFilmPreview;
use NSolutions\Filmweb\Api\Endpoint\Film\GetFilmRating;
use NSolutions\Filmweb\Api\Endpoint\Film\GetTopRoles;
use NSolutions\Filmweb\Api\Endpoint\Person\GetPerson;
use NSolutions\Filmweb\Api\Endpoint\Title\GetTitleInfo;
use NSolutions\Filmweb\Api\Mapping\PersonRefMapper;
use NSolutions\Filmweb\Config;
use NSolutions\Filmweb\Model\Genre;
use NSolutions\Filmweb\Model\Image;
use NSolutions\Filmweb\Model\Rating;
use NSolutions\Filmweb\Model\TitleInfo;
use NSolutions\Filmweb\Model\TitleType;
use NSolutions\Filmweb\Resource\FilmResource;
use NSolutions\Filmweb\Resource\PersonResource;
use NSolutions\Filmweb\Support\Data;
use NSolutions\Filmweb\Support\Markup;
use NSolutions\Filmweb\Tests\Fixtures\FakeTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilmResource::class)]
#[CoversClass(FilmEndpoint::class)]
#[CoversClass(GetTitleInfo::class)]
#[CoversClass(GetFilmPreview::class)]
#[CoversClass(GetFilmDescription::class)]
#[CoversClass(GetFilmRating::class)]
#[CoversClass(GetCriticsRating::class)]
#[CoversClass(GetFilmDates::class)]
#[CoversClass(GetTopRoles::class)]
#[CoversClass(Rating::class)]
#[UsesClass(ApiClient::class)]
#[UsesClass(Config::class)]
#[UsesClass(Data::class)]
#[UsesClass(Markup::class)]
#[UsesClass(Image::class)]
#[UsesClass(TitleInfo::class)]
#[UsesClass(PersonResource::class)]
#[UsesClass(GetPerson::class)]
#[UsesClass(PersonRefMapper::class)]
final class FilmResourceTest extends TestCase
{
    private FakeTransport $transport;
    private FilmResource $films;

    protected function setUp(): void
    {
        $this->transport = new FakeTransport();
        $client = new ApiClient($this->transport);
        $this->films = new FilmResource($client, new PersonResource($client));
    }

    public function testFetchesTitleInfo(): void
    {
        $this->transport->withFixture('/title/628/info', 'title-info');

        $info = $this->films->info(628);

        self::assertNotNull($info);
        self::assertSame('Matrix', $info->title);
        self::assertSame('The Matrix', $info->originalTitle);
        self::assertSame(TitleType::Film, $info->type);
        self::assertSame('https://fwcdn.pl/fpo/06/28/628/7685907_1.3.jpg', $info->poster?->url());
        self::assertSame('https://www.filmweb.pl/film/Matrix-1999-628', $info->url());
    }

    public function testSeriesAreServedByTheSameEndpoints(): void
    {
        $this->transport->withFixture('/title/476848/info', 'title-info-serial');

        $info = $this->films->info(476848);

        self::assertSame(TitleType::Serial, $info?->type);
        self::assertSame('serial_tv', $info->subType);
        self::assertSame('https://www.filmweb.pl/serial/Gra+o+tron-2011-476848', $info->url());
        self::assertSame('https://fwcdn.pl/fpo/68/48/476848/8145248.3.jpg', (string) $info->poster);
    }

    public function testFetchesPreview(): void
    {
        $this->transport->withFixture('/film/500891/preview', 'film-preview');

        $preview = $this->films->preview(500891);

        self::assertNotNull($preview);
        self::assertSame('Incepcja', $preview->title);
        self::assertSame('Inception', $preview->originalTitle);
        self::assertSame(148, $preview->duration);
        self::assertEquals([new Genre(10, 'Surrealistyczny'), new Genre(24, 'Thriller'), new Genre(33, 'Sci-Fi')], $preview->genres);
        self::assertSame(['US', 'GB'], $preview->countries);
        self::assertSame('Christopher Nolan', $preview->directors[0]->name);
        self::assertSame(30, $preview->mainCast[0]->id);
        self::assertTrue($preview->recommended);
        self::assertSame('https://fwcdn.pl/fpo/08/91/500891/7354571_1.3.jpg', $preview->poster?->url());
        self::assertStringStartsWith('Czasy, gdy technologia', (string) $preview->synopsis);
    }

    public function testFetchesDescriptionAsPlainText(): void
    {
        $this->transport->withFixture('/film/628/description', 'film-description');

        self::assertSame(
            'Neo (Keanu Reeves) jest genialnym hakerem. Pewnego dnia nawiązuje z nim kontakt tajemniczy Morfeusz (Laurence Fishburne).',
            $this->films->description(628),
        );
    }

    public function testFetchesRatingWithDistribution(): void
    {
        $this->transport->withFixture('/film/628/rating', 'film-rating');

        $rating = $this->films->rating(628);

        self::assertNotNull($rating);
        self::assertSame(7.6, $rating->rounded());
        self::assertSame(872129, $rating->count);
        self::assertSame(43128, $rating->wantToSeeCount);
        self::assertSame(range(1, 10), array_keys($rating->distribution));
        self::assertSame(15656, $rating->distribution[1]);
        self::assertSame(147628, $rating->distribution[10]);
    }

    public function testFetchesReleaseDates(): void
    {
        $this->transport->withFixture('/film/500891/dates', 'film-dates');

        $dates = $this->films->dates(500891);

        self::assertNotNull($dates);
        self::assertSame('2010-07-08', $dates->worldPremiere?->date->format('Y-m-d'));
        self::assertSame('GB', $dates->worldPremiere->country);
        self::assertFalse($dates->worldPremiere->cinemaRelease);
        self::assertTrue($dates->worldPublicRelease?->cinemaRelease);
        self::assertSame('PL', $dates->countryRelease?->country);
        self::assertTrue($dates->countryLastReissue?->reissue);
        self::assertSame('2020-08-12', $dates->countryLastReissue->date->format('Y-m-d'));
    }

    public function testAggregatesFilm(): void
    {
        $this->transport
            ->withFixture('/title/500891/info', 'title-info-inception')
            ->withFixture('/film/500891/preview', 'film-preview')
            ->withFixture('/film/500891/rating', 'film-rating')
            ->withFixture('/film/500891/critics/rating', 'critics-rating')
            ->withFixture('/film/500891/dates', 'film-dates');

        $film = $this->films->get(500891);

        self::assertNotNull($film);
        self::assertSame('Incepcja', $film->info->title);
        self::assertSame(148, $film->preview?->duration);
        self::assertSame(872129, $film->rating?->count);
        self::assertSame(8.1, $film->criticsRating?->rounded());
        self::assertSame([], $film->criticsRating?->distribution);
        self::assertSame('2010-07-30', $film->dates?->countryRelease?->date->format('Y-m-d'));
        self::assertCount(5, $this->transport->requestedUrls);
    }

    public function testResolvesCast(): void
    {
        $this->transport
            ->withFixture('/film/500891/top-roles', 'top-roles')
            ->withFixture('/person/30/preview', 'person-preview');

        self::assertCount(3, $this->films->topRoles(500891));

        $cast = $this->films->cast(500891, limit: 2);

        self::assertCount(2, $cast);
        self::assertSame(30, $cast[0]->role->personId);
        self::assertSame(8.9875, round($cast[0]->role->rating, 4));
        self::assertSame(20003, $cast[0]->role->votesCount);
        self::assertSame('Keanu Reeves', $cast[0]->person?->name, 'the fixture person is served for ID 30');
        self::assertNull($cast[1]->person, 'an unknown person resolves to null');
        self::assertCount(1 + 1 + 2, $this->transport->requestedUrls);
    }

    public function testReturnsNullOrEmptyForUnknownTitle(): void
    {
        self::assertNull($this->films->get(1));
        self::assertNull($this->films->rating(1));
        self::assertSame([], $this->films->cast(1));
        self::assertCount(1 + 1 + 1, $this->transport->requestedUrls, 'get() must stop after the first 404');
    }
}
