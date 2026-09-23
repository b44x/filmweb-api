<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Tests\Unit;

use DateTimeImmutable;
use NSolutions\Filmweb\Api\ApiClient;
use NSolutions\Filmweb\Api\Endpoint;
use NSolutions\Filmweb\Api\Endpoint\FilmEndpoint;
use NSolutions\Filmweb\Api\Endpoint\GetCriticsRating;
use NSolutions\Filmweb\Api\Endpoint\GetFilmDates;
use NSolutions\Filmweb\Api\Endpoint\GetFilmDescription;
use NSolutions\Filmweb\Api\Endpoint\GetFilmPreview;
use NSolutions\Filmweb\Api\Endpoint\GetFilmRating;
use NSolutions\Filmweb\Api\Endpoint\GetFilmVodOffers;
use NSolutions\Filmweb\Api\Endpoint\GetPerson;
use NSolutions\Filmweb\Api\Endpoint\GetTitleInfo;
use NSolutions\Filmweb\Api\Endpoint\GetTopRoles;
use NSolutions\Filmweb\Api\Endpoint\GetVodProviders;
use NSolutions\Filmweb\Api\Endpoint\Search;
use NSolutions\Filmweb\Config;
use NSolutions\Filmweb\Filmweb;
use NSolutions\Filmweb\Http\Response;
use NSolutions\Filmweb\Model\Genre;
use NSolutions\Filmweb\Model\PersonRef;
use NSolutions\Filmweb\Model\Rating;
use NSolutions\Filmweb\Model\SearchHit;
use NSolutions\Filmweb\Model\TitleType;
use NSolutions\Filmweb\Model\VodOffer;
use NSolutions\Filmweb\Support\Data;
use NSolutions\Filmweb\Support\ImageUrls;
use NSolutions\Filmweb\Support\Markup;
use NSolutions\Filmweb\Tests\Fixtures\FakeTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Filmweb::class)]
#[CoversClass(GetTitleInfo::class)]
#[CoversClass(GetFilmPreview::class)]
#[CoversClass(GetFilmDescription::class)]
#[CoversClass(GetFilmRating::class)]
#[CoversClass(GetCriticsRating::class)]
#[CoversClass(FilmEndpoint::class)]
#[CoversClass(GetFilmDates::class)]
#[CoversClass(GetTopRoles::class)]
#[CoversClass(GetPerson::class)]
#[CoversClass(GetVodProviders::class)]
#[CoversClass(GetFilmVodOffers::class)]
#[CoversClass(Search::class)]
#[CoversClass(SearchHit::class)]
#[CoversClass(Rating::class)]
#[CoversClass(VodOffer::class)]
#[UsesClass(ApiClient::class)]
#[UsesClass(Config::class)]
#[UsesClass(Data::class)]
#[UsesClass(ImageUrls::class)]
#[UsesClass(Markup::class)]
#[UsesClass(Response::class)]
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

    public function testFetchesTitleInfo(): void
    {
        $info = Filmweb::create((new FakeTransport())->withFixture('/title/628/info', 'title-info'))->info(628);

        self::assertNotNull($info);
        self::assertSame('Matrix', $info->title);
        self::assertSame('The Matrix', $info->originalTitle);
        self::assertSame(1999, $info->year);
        self::assertSame(TitleType::Film, $info->type);
        self::assertSame('film_cinema', $info->subType);
        self::assertSame('https://fwcdn.pl/fpo/06/28/628/7685907_1.6.jpg', $info->posterUrl);
    }

    public function testFetchesRatingWithDistribution(): void
    {
        $rating = Filmweb::create((new FakeTransport())->withFixture('/film/628/rating', 'film-rating'))->rating(628);

        self::assertNotNull($rating);
        self::assertSame(7.6, $rating->rounded());
        self::assertSame(872129, $rating->count);
        self::assertSame(43128, $rating->wantToSeeCount);
        self::assertSame(range(1, 10), array_keys($rating->distribution));
        self::assertSame(15656, $rating->distribution[1]);
        self::assertSame(147628, $rating->distribution[10]);
    }

    public function testFetchesPreviewAndStripsMarkup(): void
    {
        $transport = (new FakeTransport())
            ->withFixture('/film/500891/preview', 'film-preview')
            ->withFixture('/film/628/description', 'film-description');
        $filmweb = Filmweb::create($transport);

        $preview = $filmweb->preview(500891);

        self::assertNotNull($preview);
        self::assertSame('Incepcja', $preview->title);
        self::assertSame('Inception', $preview->originalTitle);
        self::assertSame(148, $preview->duration);
        self::assertEquals([new Genre(10, 'Surrealistyczny'), new Genre(24, 'Thriller'), new Genre(33, 'Sci-Fi')], $preview->genres);
        self::assertSame(['US', 'GB'], $preview->countries);
        self::assertSame('Christopher Nolan', $preview->directors[0]->name);
        self::assertSame(30, $preview->mainCast[0]->id);
        self::assertSame('https://fwcdn.pl/fpo/08/91/500891/7354571_1.6.jpg', $preview->posterUrl);
        self::assertStringStartsWith('Czasy, gdy technologia', (string) $preview->synopsis);

        self::assertSame(
            'Neo (Keanu Reeves) jest genialnym hakerem. Pewnego dnia nawiązuje z nim kontakt tajemniczy Morfeusz (Laurence Fishburne).',
            $filmweb->description(628),
        );
    }

    public function testAggregatesFilm(): void
    {
        $transport = (new FakeTransport())
            ->withFixture('/title/500891/info', 'title-info')
            ->withFixture('/film/500891/preview', 'film-preview')
            ->withFixture('/film/500891/rating', 'film-rating')
            ->withFixture('/film/500891/critics/rating', 'critics-rating')
            ->withFixture('/film/500891/dates', 'film-dates');

        $film = Filmweb::create($transport)->film(500891);

        self::assertNotNull($film);
        self::assertSame(148, $film->preview?->duration);
        self::assertSame(872129, $film->rating?->count);
        self::assertSame(8.1, $film->criticsRating?->rounded());
        self::assertSame([], $film->criticsRating?->distribution);
        self::assertSame('2010-07-30', $film->dates?->countryRelease?->date->format('Y-m-d'));
        self::assertCount(5, $transport->requestedUrls);
    }

    public function testFetchesReleaseDates(): void
    {
        $dates = Filmweb::create((new FakeTransport())->withFixture('/film/500891/dates', 'film-dates'))->dates(500891);

        self::assertNotNull($dates);
        self::assertSame('2010-07-08', $dates->worldPremiere?->date->format('Y-m-d'));
        self::assertSame('GB', $dates->worldPremiere->country);
        self::assertFalse($dates->worldPremiere->cinemaRelease);
        self::assertTrue($dates->worldPublicRelease?->cinemaRelease);
        self::assertSame('PL', $dates->countryRelease?->country);
        self::assertTrue($dates->countryLastReissue?->reissue);
        self::assertSame('2020-08-12', $dates->countryLastReissue->date->format('Y-m-d'));
    }

    public function testFetchesPerson(): void
    {
        $person = Filmweb::create((new FakeTransport())->withFixture('/person/87/preview', 'person-preview'))->person(87);

        self::assertNotNull($person);
        self::assertSame('Keanu Reeves', $person->name);
        self::assertSame('Keanu Charles Reeves', $person->realName);
        self::assertSame('1964-09-02', $person->birthDate?->format('Y-m-d'));
        self::assertSame('Bejrut', $person->birthPlace);
        self::assertSame(186, $person->height);
        self::assertSame('actors', $person->mainProfession);
        self::assertSame(628, $person->knownFor[0]);
        self::assertSame('https://fwcdn.pl/ppo/00/87/87/450015_1.6.jpg', $person->photoUrl);
    }

    public function testResolvesTopCast(): void
    {
        $transport = (new FakeTransport())
            ->withFixture('/film/500891/top-roles', 'top-roles')
            ->withFixture('/person/30/preview', 'person-preview');
        $filmweb = Filmweb::create($transport);

        self::assertCount(3, $filmweb->topRoles(500891));

        $cast = $filmweb->topCast(500891, limit: 2);

        self::assertCount(2, $cast);
        self::assertSame(30, $cast[0]->role->personId);
        self::assertSame(8.9875, round($cast[0]->role->rating, 4));
        self::assertSame(20003, $cast[0]->role->votesCount);
        self::assertSame('Keanu Reeves', $cast[0]->person?->name, 'fixture person is served for ID 30');
        self::assertNull($cast[1]->person, 'unknown person resolves to null');
        self::assertCount(1 + 1 + 2, $transport->requestedUrls);
    }

    public function testListsCurrentVodOffers(): void
    {
        $transport = (new FakeTransport())
            ->withFixture('/vod/providers/list', 'vod-providers')
            ->withFixture('/vod/film/500891/providers/list', 'film-vod-offers');

        $offers = Filmweb::create($transport)->whereToWatch(500891, new DateTimeImmutable('2026-09-23T12:00:00Z'));

        self::assertCount(3, $offers, 'the offer starting in 2030 is filtered out');
        self::assertSame('CHILI', $offers[0]->provider?->name);
        self::assertSame(39.9, $offers[0]->buyPrice);
        self::assertSame(9.9, $offers[0]->rentPrice);
        self::assertFalse($offers[0]->subscription);
        self::assertSame('TVSmart', $offers[1]->provider?->name);
        self::assertNull($offers[1]->buyPrice);
        self::assertSame(8.9, $offers[1]->rentPrice);
        self::assertTrue($offers[2]->subscription);
        self::assertNull($offers[2]->rentPrice);

        $afterMegogoEnds = Filmweb::create($transport)->whereToWatch(500891, new DateTimeImmutable('2026-10-01T00:00:00Z'));

        self::assertCount(2, $afterMegogoEnds);
    }

    public function testReturnsNullForUnknownTitle(): void
    {
        $transport = new FakeTransport();
        $filmweb = Filmweb::create($transport);

        self::assertNull($filmweb->film(1));
        self::assertNull($filmweb->rating(1));
        self::assertSame([], $filmweb->search('nothing'));
        self::assertSame([], $filmweb->topCast(1));
        self::assertCount(1 + 1 + 1 + 1, $transport->requestedUrls, 'film() must stop after the first 404');
    }

    public function testSendsLocaleAndJsonHeaders(): void
    {
        $transport = new FakeTransport();

        Filmweb::create($transport, new Config(locale: 'en_US'))->info(1);

        self::assertSame('https://www.filmweb.pl/api/v1/title/1/info', $transport->requestedUrls[0]);
        self::assertSame('en_US', $transport->lastHeaders['x-locale']);
        self::assertSame('application/json', $transport->lastHeaders['Accept']);
    }

    public function testRunsCustomEndpointsAndRawPaths(): void
    {
        /** @var Endpoint<string> $endpoint */
        $endpoint = new class implements Endpoint {
            public function path(): string
            {
                return '/person/87/info';
            }

            public function query(): array
            {
                return [];
            }

            public function map(Data $data, ImageUrls $images): string
            {
                return $data->string('name');
            }
        };

        $transport = (new FakeTransport())->with('/person/87/info', new Response(200, '{"name":"Keanu Reeves"}'));
        $filmweb = Filmweb::create($transport);

        self::assertSame('Keanu Reeves', $filmweb->call($endpoint));
        self::assertSame(['name' => 'Keanu Reeves'], $filmweb->raw('/person/87/info')?->toArray());
    }
}
